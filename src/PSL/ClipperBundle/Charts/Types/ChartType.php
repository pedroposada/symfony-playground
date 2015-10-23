<?php
namespace PSL\ClipperBundle\Charts\Types;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\SurveyChartMap;
use PSL\ClipperBundle\Utils\GeoMapper;

abstract class ChartType
{
  protected $container;
  protected $logger;
  protected $em; // entity manager
  protected $params;
  protected $responses;
  protected $machine_name;
  protected $survey_chart_map;
  protected $explode_tree;
  protected $geoMapper;
  public $data_table;

  /**
   * Event variables
   */
  protected $brands;
  protected $map;
  protected $qcode;
  protected $drill_down;
  protected static $net_promoters           = 'NPS';
  protected static $decimal_point           = 2;
  protected static $net_promoters_cat_range = array(
    'detractor' => array(0, 1, 2, 3, 4, 5, 6),
    'passive'   => array(7, 8),
    'promoter'  => array(9, 10),
  );

  public function __construct(ContainerInterface $container, $machine_name) {
    $this->container        = $container;
    $this->em               = $container->get('doctrine')->getManager();
    $this->logger           = $container->get('monolog.logger.clipper');
    $this->params           = $container->getParameter('clipper');
    $this->machine_name     = $machine_name;
    $this->survey_chart_map = $container->get('survey_chart_map');
    $this->explode_tree     = $container->get('explode_tree');
    $this->geoMapper        = new GeoMapper();
  }

  public function onDataTable(ChartEvent $event, $eventName, EventDispatcherInterface $dispatcher) {
    if ($event->getChartMachineName() === $this->machine_name) {
      $this->logger->debug("eventName: {$eventName}");

      //prep generals details
      $this->brands = $event->getBrands();
      $this->map    = $this->survey_chart_map->map($event->getSurveyType());
      $this->qcode  = $this->map[$event->getChartMachineName()];
      
      $responses = $event->getData();
      //get available drilldown filters
      $drilldown = $this->extractAvailableFilters($responses);
      $event->setDrillDown($drilldown);
      
      //filter down      
      $drilldown = $event->getFilters();
      if (!empty($drilldown)) {
        $this->filterResponsesDrillDown($responses, $drilldown);
        $event->setFilters($drilldown);
        $event->setCountFiltered($responses->count());
      }
      
      // @todo: review the needs for validate
      // if (empty($event->getCountFiltered())) {        
      //   throw error of filter down render no result
      // }
      
      $event->setDataTable($this->dataTable($event));
    }
  }
  
  private function extractAvailableFilters($responses) {
    $a_reponse = $responses->first();    
    $markets = $regions = $specialties = array();
    
    //get markets & specialties from a response
    foreach (array('markets', 'specialties') as $type) {
      $$type = $a_reponse->getFirstqgroup()->getFormDataByField($type);
    }
    
    //identify markets region & countries
    //end result: markets will only holds countries & countries out of region(s)
    if (!empty($markets)) {
      $map_regions = $this->geoMapper->getRegions();
      foreach ($markets as $key => $market) {
        if (in_array($market, $map_regions)) {
          unset($markets[$key]);
          $regions[] = $market;
          $reg_countries = $this->geoMapper->getCountries($market);
          $markets = array_merge($markets, $reg_countries);
        }
      }
    }
    
    //reorganize in drilldown format
    $drilldown = array();
    foreach (array('markets' => 'countries', 'specialties' => 'specialties', 'regions' => 'regions') as $type => $ddType) {
      $drilldown[$ddType] = $$type;
    }
    
    return $drilldown;
  }
  
  /**
   * Method to filter down responses.
   * @method filterResponsesDrillDown
   *
   * @todo : review case-sensitive/strict comparison
   * @todo : a country filter within selected region
   * 
   * @param  array PSL\ClipperBundle\LimeSurveyResponse ArrayCollection &$responses
   * @param  array &$drilldown
   *
   * @return array
   *    Filter used
   */
  private function filterResponsesDrillDown(&$responses, &$drilldown) {
    $drilldown['countries'] = array();
    
    if (!empty($drilldown['region'])) {
      $drilldown['countries'] = $this->geoMapper->getCountries($drilldown['region']);
    }
    $drilldown['countries'] = array_merge($drilldown['countries'], array($drilldown['country']));
    $drilldown['countries'] = array_unique($drilldown['countries']);
    $drilldown['countries'] = array_filter($drilldown['countries']);
    
    foreach ($responses as $index => $response) {
      $sheet_data = $response->getFirstqproject()->getSheetDataUnserialized();
      if ((!empty($drilldown['region'])) && ($drilldown['region'] == $sheet_data['market'])) {
        //selected the same region
      }
      elseif ((!empty($drilldown['countries'])) && (in_array($sheet_data['market'], $drilldown['countries']) == FALSE)) {
        $responses->remove($index);
        continue;
      }      
      
      if ( //@todo: review if empty sheet_data
          (empty($sheet_data['specialty'])) || 
          ((!empty($drilldown['specialty'])) && (strtolower($drilldown['specialty']) != strtolower($sheet_data['specialty'])))
        ) {
        $responses->remove($index);
      }
    }
  }

  /**
   * Helper method to filter list of answers to given map.
   * @method filterAnswersToQuestionMap
   *
   * This method will return list of answers assigned to keyed brands.
   *
   * @param  array $answers
   *    List of answers, keyed by question index.
   *    Use $response->getResponseDecoded()
   *
   * @param  boolean|string $convert
   *    Cast & sanitize result flag.
   *    - FALSE; just return raw result; normally in string.
   *    - string; - int; cast to integer
   *    - string; - str; cast to string
   *
   * @param  boolean|string|array $qcode
   *    Question index ID(s)
   *    - FALSE; will use class defined @var $this->qcode
   *    - string/array provide the list.
   *
   * @param  boolean|array $brands
   *    List of brands within the questions.
   *    - FALSE; will use class defined @var $this->brands / @var $event->getBrands()
   *    - array provide the brand list
   *
   * @return array
   */
  protected function filterAnswersToQuestionMap($answers, $convert = FALSE, $qcode = FALSE, $brands = FALSE) {
    if ((empty($qcode)) && (!empty($this->qcode))) {
      $qcode = $this->qcode;
    }
    if ((empty($brands)) && (!empty($this->brands))) {
      $brands = $this->brands;
    }

    if ((empty($qcode)) || (empty($brands)) || (empty($answers))) {
      return FALSE;
    }

    $cp_answers = $answers;
    $multi_structure = FALSE;
    $answers = array_filter($cp_answers, function($key) use ($qcode) {
      $method = (is_array($qcode) ? 'in_array' : 'strpos');
      return ($method($key, $qcode) !== FALSE);
    }, ARRAY_FILTER_USE_KEY);

    //if given array but need to get using strpos; use by DNA slide
    //see @var $multi_structure
    if (empty($answers)) {
      $answers = array();
      foreach ($qcode as $qc) {
        $answers[$qc] = array_filter($cp_answers, function($key) use ($qc) {
          return (strpos($key, $qc) !== FALSE);
        }, ARRAY_FILTER_USE_KEY);
      }

      //still failing
      if (empty($answers)) {
        return array();
      }
      $multi_structure = TRUE;
    }
    unset($cp_answers);

    //check if flipped
    $ids = array_keys($brands);
    $ids = end($ids);
    if (is_string($ids)) {
      $brands = array_flip($brands);
    }

    $result = array();
    if (!$multi_structure) {
      $result = array_combine($brands, array_values($answers));
      if (!empty($convert)) {
        $result = $this->formatAnswerResult($convert, $result);
      }
    }
    else {
      $result = array_combine($brands, array_values($answers));
      foreach ($result as $brand => $answers) {
        $answers = array_values($answers);
        if (!empty($convert)) {
          $answers = $this->formatAnswerResult($convert, $answers);
        }
        $result[$brand] = $answers;
      } //if multi_structure
    }

    return $result;
  }

  /**
   * Helper method to rounding up the given value.
   * @method roundingUpValue
   *
   * @param  integer $value
   * @param  boolean|int $decPoint
   *    Assign decimal point count, or else @var self::$decimal_point
   *
   * @param  boolean $force_string
   *    Flag to forcing the decimal point, in string.
   *
   * @return float|string
   */
  protected function roundingUpValue($value = 0, $decPoint = FALSE, $force_string = FALSE) {
    if ($decPoint === FALSE) {
      $decPoint = self::$decimal_point;
    }
    if ($force_string) {
      return number_format($value, $decPoint, '.', ',');
    }
    return round($value, $decPoint, PHP_ROUND_HALF_UP);
  }

  /**
   * Helper method to identify Respondent categories type based on user answer.
   * @method identifyRespondentCategory
   *
   * This value is expected in integer (or string), between 0-10;
   * See @var this::$net_promoters_cat_range
   *
   * Note: If the given answer is out of the range;
   *  > 10; it will consider as "promoter"
   *  < 0; it will consider as "detector" (always)
   *
   * @param  integer|string $respondentAnswer
   * @param  boolean|string|int $validate_to
   *    Flag to determine the return type/value.
   *    @see  keys on @var this::$net_promoters_cat_range
   *    - FALSE; return as string key; category (singular).
   *    - string; return in boolean, compare result with key within given range.
   *    - int; return in boolean, compare result with indexed key within given range.
   *
   * @return string|boolean
   */
  protected function identifyRespondentCategory($respondentAnswer = 0, $validate_to = FALSE) {
    //clean up inputs
    $respondentAnswer = (int) $respondentAnswer;
    $int_ids = array_keys(self::$net_promoters_cat_range);
    if (!empty($validate_to)) {
      if (is_numeric($validate_to)) {
        $validate_to = $int_ids[$validate_to];
      }
      else {
        $validate_to = (string) $validate_to;
        $validate_to = trim($validate_to);
        $validate_to = strtolower($validate_to);
      }
    }

    //prep data
    $values = array();
    static $net_promoters_cat_range_values;
    if (isset($net_promoters_cat_range_values)) {
      $values = $net_promoters_cat_range_values;
    }
    else {
      array_walk(self::$net_promoters_cat_range, function($set, $type) use (&$values) {
        $new = array_combine($set, array_fill(0, count($set), $type));
        $values = array_merge($values, $new);
      });
      $net_promoters_cat_range_values = $values;
    }

    // in range
    if (isset($values[$respondentAnswer])) {
      if (!empty($validate_to)) {
        return ($validate_to == $values[$respondentAnswer]);
      }
      return $values[$respondentAnswer];
    }

    //out of range
    $values = array_keys($values);
    $result = array_slice($int_ids, 0, 1);
    if ($respondentAnswer > max($values)) {
      $result = array_slice($int_ids, -1, 1);
    }
    $result = end($result);
    if (!empty($validate_to)) {
      return ($validate_to == $result);
    }
    return $result;
  }

  /**
   * Helper method to extract Answer into valid format.
   * @method formatAnswerResult
   *
   * @param  string $type
   * @param  array $answers
   *
   * @return array
   */
  protected function formatAnswerResult($type, $answers) {
    $type = strtolower($type);
    switch ($type) {
      case 'int':
      case 'integer':
        $answers = array_map('intval', $answers);
        break;

      case 'str':
      case 'string':
      case 'trim':
        $answers = array_map('trim', $answers);
        break;

      case 'y/n':
        $answers = array_map('strtolower', $answers);
        $answers = array_map('trim', $answers);
        array_walk($answers, function(&$value,  $key) {
          $value = ($value == 'yes' ? 1 : 0);
        });
        break;
    }
    return $answers;
  }
  
  /**
   * Sanitizes a title, replacing whitespace and a few other characters with dashes.
   * @method sanitiveComment
   * 
   * Limits the output to alphanumeric characters, underscore (_) and dash (-).
   * Whitespace becomes a dash.
   * 
   * Adopted from WordPress sanitize_title_with_dashes()
   *
   * @param  string $title
   *
   * @return string
   */
  protected function sanitiveComment($title) {
    $title = strip_tags($title);
    $title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
    $title = str_replace('%', '', $title);
    $title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);
    $title = strtolower($title);
    $title = preg_replace('/&.+?;/', '', $title);
    $title = str_replace('.', '-', $title);
    $title = str_replace( array( '%c2%a0', '%e2%80%93', '%e2%80%94' ), '-', $title );
    $title = str_replace( array(
      '%c2%a1', '%c2%bf',
      '%c2%ab', '%c2%bb', '%e2%80%b9', '%e2%80%ba',
      '%e2%80%98', '%e2%80%99', '%e2%80%9c', '%e2%80%9d',
      '%e2%80%9a', '%e2%80%9b', '%e2%80%9e', '%e2%80%9f',
      '%c2%a9', '%c2%ae', '%c2%b0', '%e2%80%a6', '%e2%84%a2',
      '%c2%b4', '%cb%8a', '%cc%81', '%cd%81',
      '%cc%80', '%cc%84', '%cc%8c',
    ), '', $title );
    $title = str_replace( '%c3%97', 'x', $title );
    $title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
    $title = preg_replace('/\s+/', '-', $title);
    $title = preg_replace('|-+|', '-', $title);
    $title = trim($title, '-');
    return $title;
  }

  /**
   * @param ChartEvent $event ChartEvent
   *
   * @return array charts dataTable
   */
  abstract protected function dataTable(ChartEvent $event);
}
