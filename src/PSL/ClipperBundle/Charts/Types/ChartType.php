<?php
namespace PSL\ClipperBundle\Charts\Types;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\SurveyChartMap;

abstract class ChartType
{
  protected $container;
  protected $logger;
  protected $em; // entity manager
  protected $params;
  protected $responses;
  protected $chart_type;
  protected $survey_chart_map;
  protected $explode_tree;
  public $data_table;

  /**
   * Event variables
   */
  protected $brands;
  protected $map;
  protected $qcode;
  protected static $net_promoters           = 'net_promoters';
  protected static $decimal_point           = 2;
  protected static $net_promoters_cat_range = array(
    'detractor' => array(0, 1, 2, 3, 4, 5, 6),
    'passive'   => array(7, 8),
    'promoter'  => array(9, 10),
  );

  public function __construct(ContainerInterface $container, $chart_type)
  {
    $this->container        = $container;
    $this->em               = $container->get('doctrine')->getManager();
    $this->logger           = $container->get('monolog.logger.clipper');
    $this->params           = $container->getParameter('clipper');
    $this->chart_type       = $chart_type;
    $this->survey_chart_map = $container->get('survey_chart_map');
    $this->explode_tree     = $container->get('explode_tree');
  }

  public function onDataTable(ChartEvent $event, $eventName, EventDispatcherInterface $dispatcher)
  {
    if ($event->getChartType() === $this->chart_type) {
      $this->logger->debug("eventName: {$eventName}");

      //prep generals details
      $this->brands = $event->getBrands();
      $this->map    = $this->survey_chart_map->map($event->getSurveyType());
      $this->qcode  = $this->map[$event->getChartType()];

      $event->setDataTable($this->dataTable($event));
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

    //if given array but need to get using strpos; use by AssociateCategoriesImportance
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
   * @param ChartEvent $event ChartEvent
   *
   * @return array charts dataTable
   */
  abstract protected function dataTable(ChartEvent $event);
}
