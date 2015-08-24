<?php
/**
 * Machine Name      = DoctorPromotingBrands
 * Service Name      = clipper.chart.doctor_promoting_brands
 * Targeted Question = G003Q001
 * Targeted Template = ./src/PSL/ClipperBundle/Resources/views/Charts/doctor_promoting_brands.html.twig
 */
namespace PSL\ClipperBundle\Charts\Types;

use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\Types\ChartType;

class DoctorPromotingBrands extends ChartType {

  private $map        = array();
  private $qcode      = '';

  private $brands     = array();
  private $respondent = array();

  private static $decimalPoint = 2;

  private $promoting  = array(
    'ds' => array(
      'label'  => 'Dissatisfied',
      'append' => '(0 brands promoted)',
      'count'  => 0,
      'perc'   => 0,
      'show'   => 0,
    ),
    'sa' => array(
      'label'  => 'Satisfied',
      'append' => '(>0 brands promoted)',
      'count'  => 0,
      'perc'   => 0,
      'show'   => 0,
    ),
    'se' => array(
      'label'  => 'Satisfied (Exclusive)',
      'append' => '(1 brand promoted)',
      'count'  => 0,
      'perc'   => 0,
      'show'   => 0,
    ),
    'ss' => array(
      'label'  => 'Satisfied (Shared)',
      'append' => '(>1 brands promoted)',
      'count'  => 0,
      'perc'   => 0,
      'show'   => 0,
    ),
  );

  /**
   * Method call to return chart data.
   * @method dataTable
   *
   * @see  ChartType dataTable
   *
   * @param  ChartEvent $event
   *
   * @return array
   *     Google Chart array in Visualization format
   */
  public function dataTable(ChartEvent $event) {
    //prep other attributes
    $this->map   = $this->survey_chart_map->map($event->getSurveyType());
    $this->qcode = $this->map[$event->getChartType()];
    $this->brands = $event->getBrands();

    //extract respondent
    foreach ($event->getData() as $response) {
      //update @var $this->brands
      $this->extractRespondent($response);
    }

    //#final-calculation; calculate the aggregated count into parentage
    $total = $this->promoting['ds']['count'] + $this->promoting['sa']['count'];
    foreach ($this->promoting as $ty => $set) {
      $this->promoting[$ty]['perc'] = $this->roundingUpValue((($set['count'] / $total) * 100));
      $this->promoting[$ty]['show'] = $this->roundingUpValue($this->promoting[$ty]['perc'], 0, TRUE) . '%';
    }

    return $this->promoting;
  }

  /**
   * Method to extract a respondent answer.
   * @method extractRespondent
   *
   * Process will populate
   * - @var $this->respondent
   *
   * Post-format $this->brands:
   *   $this->respondent
   *     TOKEN
   *       BRAND => ANSWER-VALUE
   *       BRAND => ANSWER-VALUE
   *     TOKEN
   *       BRAND => ANSWER-VALUE
   *       BRAND => ANSWER-VALUE
   *
   * @param  LimeSurveyResponse $response
   *
   * @return void
   */
  private function extractRespondent(LimeSurveyResponse $response) {
    //getting respondent token
    $lstoken = $response->getLsToken();

    //getting answers
    $answers = $response->getResponseDecoded();

    //filtering answers to which related question
    $qcode = $this->qcode; //avoid lexical
    $answers = array_filter($answers, function($key) use ($qcode) {
      return (strpos($key, $qcode) !== FALSE);
    }, ARRAY_FILTER_USE_KEY);
    $answers = array_values($answers);

    //values assignments
    foreach ($this->brands as $index => $brand) {
      //respondent overall
      if (!isset($this->respondent[$lstoken])) {
        $this->respondent[$lstoken] = array();
      }
      $this->respondent[$lstoken][$brand] = (int) $answers[$index];
    }

    //convert brand's answers into score by each respondent
    $this->calculateRespondentSatifaction($this->respondent[$lstoken]);
  }

  /**
   * Method to determine Respondent Satisfaction.
   * @method calculateRespondentSatifaction
   *
   * Process will populate
   * - @var $this->promoting
   *     TYPE
   *       COUNT = X
   *     TYPE
   *       COUNT = X
   *
   * @param  array $brandsAnswer
   *
   * @return void
   */
  private function calculateRespondentSatifaction($brandsAnswer = array()) {
    $promote_count = array_filter($brandsAnswer);
    $promote_count = count($promote_count);
    if ($promote_count >= 1) {
      $this->promoting['sa']['count']++;
      if ($promote_count == 1) {
        $this->promoting['se']['count']++;
      }
      else {
        $this->promoting['ss']['count']++;
      }
      return;
    }
    $this->promoting['ds']['count']++;
    return;
  }

  /**
   * Helper method to rounding up the given value.
   * @method roundingUpValue
   *
   * @param  integer $value
   * @param  boolean|int $decPoint
   *    Assign decimal point count, or else @var self::$decimalPoint
   * @param  boolean $force_string
   *    Flag to forcing the decimal point, in string.
   *
   * @return float|string
   */
  private function roundingUpValue($value = 0, $decPoint = FALSE, $force_string = FALSE) {
    if ($decPoint === FALSE) {
      $decPoint = self::$decimalPoint;
    }
    if ($force_string) {
      return number_format($value, $decPoint, '.', ',');
    }
    return round($value, $decPoint, PHP_ROUND_HALF_UP);
  }
}