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

  public function __construct(ContainerInterface $container, $machine_name) 
  {
    $this->container        = $container;
    $this->em               = $container->get('doctrine')->getManager();
    $this->logger           = $container->get('monolog.logger.clipper');
    $this->params           = $container->getParameter('clipper');
    $this->machine_name     = $machine_name;
    $this->survey_chart_map = $container->get('survey_chart_map');
    $this->explode_tree     = $container->get('explode_tree');
    $this->geoMapper        = new GeoMapper();
  }
  
  /**
   * Event will invoke this, by kick-start the concrete class.
   * @method onDataTable
   *
   * @param  ChartEvent $event
   * @param  string $eventName
   * @param  EventDispatcherInterface $dispatcher
   *
   * @return void
   */
  public function onDataTable(ChartEvent $event, $eventName, EventDispatcherInterface $dispatcher) 
  {
    // only apply to request machine name
    if ($event->getChartMachineName() === $this->machine_name) {
      $this->logger->debug("eventName: {$eventName}");

      // get & prep responses
      $this->responses = $event->getData();
            
      // get brands
      $this->brands = $event->getBrands();
      
      // get map by chart type
      $qcode_collection = $this->extractFirstDecodedReponse(TRUE);
      $this->map = $this->survey_chart_map->map($event->getSurveyType(), $qcode_collection);
      
      // filter to current map only
      $this->qcode  = $this->map[$event->getChartMachineName()];
      
      // get available drilldown filters
      $drilldown = $this->extractAvailableResponsesFilters();
      $event->setDrillDown($drilldown);
      
      // filter down
      $drilldown = $event->getFilters();
      if (!empty($drilldown)) {
        $this->filterResponsesDrillDown($drilldown);
        $event->setFilters($drilldown);
        $event->setCountFiltered($this->responses->count());
      }
      
      // set new data table on event      
      $event->setDataTable($this->dataTable($event));
    }
  }
  
  /**
   * Method to extract supported drilldown filter values.
   * @method extractAvailableResponsesFilters
   *
   * @return array
   */
  private function extractAvailableResponsesFilters() 
  {
    $a_reponse = $this->responses->first();    
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
    $drillset = array(
      'markets'     => 'countries',
      'specialties' => 'specialties',
      'regions'     => 'regions',     
    );
    foreach ($drillset as $type => $ddType) {
      $drilldown[$ddType] = $$type;
    }
    
    return $drilldown;
  }
  
  /**
   * Method to return the first decoded responses
   * @method extractFirstDecodedReponse
   *
   * @param  boolean $get_keys_only
   *
   * @return array
   */
  private function extractFirstDecodedReponse($get_keys_only = FALSE)
  {
    $first = FALSE;
    if ($this->responses->count()) {
      // get first responses decoded
      $first = $this->responses->first()->getResponseDecoded();
      // get only the keys
      if ($get_keys_only) {
        $first = array_keys($first);        
      }
    }
    return $first;
  }
  
  /**
   * Method to filter down responses.
   * @method filterResponsesDrillDown
   *
   * @todo : review case-sensitive/strict comparison
   * @todo : a country filter within selected region
   *
   * @param  array &$drilldown
   *
   * @return array
   *    Filter used
   */
  private function filterResponsesDrillDown(&$drilldown) 
  {
    $drilldown['countries'] = array();
    
    if (!empty($drilldown['region'])) {
      $drilldown['countries'] = $this->geoMapper->getCountries($drilldown['region']);
    }
    $drilldown['countries'] = array_merge($drilldown['countries'], array($drilldown['country']));
    if (empty($drilldown['region'])) {
      foreach ($drilldown['countries'] as $country) {
        if ((empty($country)) || (!is_string($country))) {
          continue;
        }
        $found = $this->geoMapper->getCountries($country);
        if (!empty($found)) {
          $drilldown['countries'] = array_merge($drilldown['countries'], $found);
        }
      }
    }
    $drilldown['countries'] = array_unique($drilldown['countries']);
    $drilldown['countries'] = array_filter($drilldown['countries']);
    
    foreach ($this->responses as $index => $response) {
      $sheet_data = $response->getFirstqproject()->getSheetDataUnserialized();
      if ((!empty($drilldown['region'])) && ($drilldown['region'] == $sheet_data['market'])) {
        //selected the same region
      }
      elseif ((!empty($drilldown['countries'])) && (in_array($sheet_data['market'], $drilldown['countries']) == FALSE)) {
        $this->responses->remove($index);
        continue;
      } // if      
      
      if ( //@todo: review if empty sheet_data
          (empty($sheet_data['specialty'])) || 
          (
            (!empty($drilldown['specialty'])) 
            && 
            (strtolower($drilldown['specialty']) != strtolower($sheet_data['specialty']))
          )
        )
      {
        $this->responses->remove($index);
      } // if
    } // foreach
  }
  
  /**
   * Method to extract an answer into Brands.
   * @method filterAnswersToQuestionMapViaBrand
   *
   * @param  array $answers
   * @param  boolean $convert
   *
   * @return array
   */
  protected function filterAnswersToQuestionMapViaBrand($answers, $convert = FALSE)
  {
    // Don't process empty answer
    if (empty($answers)) {
      return FALSE;
    }
    
    // Realign answer to qCode. 
    $qcode = $this->qcode;
    foreach ($answers as $key => $answer) {
      if (is_array($this->qcode)) {
        if (!in_array($key, $this->qcode)) {
          unset($answers[$key]);
        }
        continue; // foreach
      }
      if (strpos($key, $this->qcode) === FALSE) {
        unset($answers[$key]);
      }
    } // foreach
    $answers = array_values($answers);
    
    // Assign to answer to brand.
    //  ignoring other answer
    //  - had more than brand count
    //  - ie: "Others"
    $result = array();
    if (count($this->brands) != count($answers)) {
      foreach ($this->brands as $index => $brand) {
        $result[$brand] = $answers[$index];
      }
    }
    else {
      $result = array_combine($this->brands, $answers);
    }
    
    // Convert answers if needed
    if (!empty($convert)) {
      $result = $this->formatAnswerResult($convert, $result);
    }
    
    return $result;
  }
  
  /**
   * Method to extract an answer into Brands while value categorized into NetPromoter value.
   * @method filterAnswersToQuestionMapViaNetPromoter
   *
   * @param  array $answers
   * @param  boolean|integer $append
   *    This param allow if number of brand is more than given answer for NPS,
   *    Give an Integer value to assigned to orphan brands.
   *
   * @return array
   */
  protected function filterAnswersToQuestionMapViaNetPromoter($answers, $append = FALSE)
  {
    // Don't process empty answer
    if (empty($answers)) {
      return FALSE;
    }
    
    // Realign answer to NPS categories. 
    $nps_map = $this->map[self::$net_promoters];
    foreach ($answers as $key => $answer) {
      if (is_array($nps_map)) {
        if (!in_array($key, $nps_map)) {
          unset($answers[$key]);
        } // foreach
        continue;
      }
      if (strpos($key, $nps_map) === FALSE) {
        unset($answers[$key]);
      }
    } // foreach
    $answers = array_values($answers);
    
    // Assign to answer to brand.
    //  ignoring other answer
    //  - had more than brand count
    //  - ie: "Others"
    $result = array();
    if (count($this->brands) != count($answers)) {
      foreach ($this->brands as $index => $brand) {
        if ((!isset($answers[$index])) && ($append !== FALSE)) {
          $result[$brand] = $append;
          continue;
        }
        $result[$brand] = $answers[$index];
      }
    }
    else {
      $result = array_combine($this->brands, $answers);
    }
    
    // Convert answers of NPS categories into integer
    $result = $this->formatAnswerResult('int', $result);
    
    return $result;
  }
  
  /**
   * Method to extract an answer into Messages while value categorized into Y/N integer value.
   * @method filterAnswersToQuestionMapIntoViaMessages
   *
   * @param  array $answers
   * @param  array $messages
   *
   * @return array
   */
  protected function filterAnswersToQuestionMapIntoViaMessages($answers, $messages)
  {
    // Don't process empty answer or messages
    if ((empty($answers)) || (empty($messages))) {
      return FALSE;
    }
    
    // get related answers
    $cp_answers = $answers;
    $answers = array();
    foreach ($this->qcode as $qc) {
      $answers[$qc] = array_filter($cp_answers, function($key) use ($qc) {
        return (strpos($key, $qc) !== FALSE);
      }, ARRAY_FILTER_USE_KEY);
    }
    unset($cp_answers);
    $answers = array_values($answers);
    $answers = array_map('array_values', $answers);
    
    // Convert answers of Messages into integer
    foreach ($answers as $ansi => $answer) {
      $answers[$ansi] = $this->formatAnswerResult('y/n', $answer);
    }
    
    // realign answers, brand and messages
    $messages = array_values($messages);
    $result = array_combine($this->brands, array_fill(0, count($this->brands), array()));    
    foreach ($this->brands as $brand_index => $brand) {
      // notice this will result the last answer / more than brand count will be ignore
      // - this applied to "None of these" answer
      foreach ($messages as $msg_index => $message) {
        $result[$brand][] = $answers[$msg_index][$brand_index];
      }
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
  protected function roundingUpValue($value = 0, $decPoint = FALSE, $force_string = FALSE) 
  {
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
   *
   * @return string
   */
  protected function identifyRespondentCategory($respondentAnswer = 0) 
  {
    //clean up input
    $respondentAnswer = (int) $respondentAnswer;
    
    //prep / get static data
    static $net_promoters_cat_range_values;
    $values = array();
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
      return $values[$respondentAnswer];
    }
    
    //out of range
    $values = array_keys($values);
    $result = array_slice($int_ids, 0, 1);
    if ($respondentAnswer > max($values)) {
      $result = array_slice($int_ids, -1, 1);
    }
    $result = end($result);
        
    return $result;
  }
  
  /**
   * Helper method to validate Respondent categories to given string.
   * @method validateRespondentCategory
   * 
   * @uses $this->identifyRespondentCategory()
   *
   * @param  string $validate_to
   *    - string; return in boolean, compare result with key within given range.
   *    - int; return in boolean, compare result with indexed key within given range.
   *
   * @return boolean
   */
  protected function validateRespondentCategory($respondentAnswer = 0, $validate_to) 
  {
    // Identify Respondent Category
    $respondentAnswer = $this->identifyRespondentCategory($respondentAnswer);    
    
    // Sanitize validation
    if (is_numeric($validate_to)) {
      $validate_to = $int_ids[$validate_to];
    }
    else {
      $validate_to = (string) $validate_to;
      $validate_to = trim($validate_to);
      $validate_to = strtolower($validate_to);
    }
    
    return ($validate_to == $respondentAnswer);
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
  protected function formatAnswerResult($type, $answers) 
  {
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
  protected function sanitiveComment($title) 
  {
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
