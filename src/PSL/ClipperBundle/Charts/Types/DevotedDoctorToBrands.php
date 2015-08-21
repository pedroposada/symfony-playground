<?php
/**
 * Machine Name      = DevotedDoctorToBrands
 * Service Name      = clipper.chart.devoted_doctor_to_brands
 * Targeted Question = G003Q001
 * Targeted Template = ./src/PSL/ClipperBundle/Resources/views/Charts/devoted_doctor_to_brands.html.twig
 */
namespace PSL\ClipperBundle\Charts\Types;

use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\Types\ChartType;

class DevotedDoctorToBrands extends ChartType {
  private $map        = array();
  private $qcode      = '';

  private $brands     = array();
  private $respondent = array();

  private static $decimalPoint = 2;

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

    //extract respondent
    foreach ($event->getData() as $response) {
      //initialize brands collection; once
      if (empty($this->brands)) {
        $brands = $response->getFirstqgroup()->getFormDataByField('brands');
        foreach ($brands as $brand) {
          $this->brands[$brand] = array();
        }
      }
      //update @var $this->brands
      $this->extractRespondent($response);
    }

    //#final-calculation
    $overall_total = $overall_count = 0;
    foreach ($this->brands as $brand => $respondent) {
      $total = array_sum($respondent);
      $overall_total += $total;
      $count = count($respondent);
      $overall_count += $count;
      $this->brands[$brand] = $this->roundingUpValue(($total / $count));
    }
    $overall_avg = $this->roundingUpValue(($overall_total / $overall_count));

    //sorting
    arsort($this->brands);

    //data formation
    $dataTable = array(
      'cols' => array(
        array(
          'label' => '',
          'type'  => 'string',
        ),
        array(
          'label' => '',
          'type'  => 'number',
        ),
        array(
          'type' => 'string',
          'p'    => array('role' => 'annotation'),
        ),
        array(
          'type' => 'string',
          'p'    => array('role' => 'style')
        ),
      ),
      'rows' => array(
        array(
          'c' => array(
            array('v' => 'Mean'),
            array('v' => $overall_avg),
            array('v' => $this->roundingUpValue($overall_avg, TRUE)),
            array('v' => ''), //color will be set on template
          ),
        ),
      ),
    );
    foreach ($this->brands as $brand => $loyalty) {
      $dataTable['rows'][] = array(
        'c' => array(
          array('v' => $brand),
          array('v' => $loyalty),
          array('v' => $this->roundingUpValue($loyalty, TRUE)),
          array('v' => ''), //color will be set on template
        ),
      );
    }
    return $dataTable;
  }

  /**
   * Method to extract a respondent answer.
   * @method extractRespondent
   *
   * Process will populate
   * - @var $this->brands
   * - @var $this->respondent
   *
   *
   * Post-format $this->brands:
   *
   *   $this->brands
   *     BRAND
   *       TOKEN => SCORE
   *       TOKEN => SCORE
   *     BRAND
   *       TOKEN => SCORE
   *     ...
   *
   * Note: This format will change once at #final-calculation
   *   $this->brands
   *     BRAND => SCORE
   *     BRAND => SCORE
   *     ...
   *
   * Post-format $this->brands:
   *   $this->respondent
   *     TOKEN
   *       BRAND => ANSWER-VALUE
   *       BRAND => ANSWER-VALUE
   *     TOKEN
   *       BRAND => ANSWER-VALUE
   *       BRAND => ANSWER-VALUE
   *     ...
   *
   * @param  LimeSurveyResponse $response
   *
   * @return void
   */
  private function extractRespondent(LimeSurveyResponse $response) {
    //getting repondent token
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
    $index = 0;
    foreach ($this->brands as $brand => $resp) {
      //brands overall
      if (!isset($this->brands[$brand][$lstoken])) {
        $this->brands[$brand][$lstoken] = 0;
      }
      //respondent overall
      if (!isset($this->respondent[$lstoken])) {
        $this->respondent[$lstoken] = array();
      }
      $this->respondent[$lstoken][$brand] = (int) $answers[$index];
      $index++;
    }

    //convert brand into score by each respondent
    $this->calculateRespondentScore($lstoken, $this->respondent[$lstoken]);
  }

  /**
   * Method to determine Brands Score.
   * @method calculateRespondentScore
   *
   * This process will change value for;
   * - @var $this->brands, based on respondent answers
   *
   * |---------|-----------|---------------------------|
   * | Answers |  Category |           Score           |
   * |---------|-----------|---------------------------|
   * | 0 to 6  | Detractor | 1                         |
   * | 7 to 8  | Passive   | 2                         |
   * | 9 to 10 | Promoter  | 3 + (2 / OtherBrandCount) |
   * |---------|-----------|---------------------------|
   *
   * @param  string $lstoken
   * @param  array $brandsAnswer
   *
   * @return void
   */
  private function calculateRespondentScore($lstoken, $brandsAnswer = array()) {
    foreach ($brandsAnswer as $brand => $answer) {
      $brandsAnswer[$brand] = 0; //blank

      if (in_array($answer, range(1, 6))) {
        //Detractor
        $brandsAnswer[$brand] = 1;
      }
      elseif (in_array($answer, array(7, 8))) {
        //Passive
        $brandsAnswer[$brand] = 2;
      }
      elseif (in_array($answer, array(9, 10))) {
        //Promoter
        $OtherBrandCount = array_map(function($value) {
          return ($value > 1 ? 1 : 0);
        }, $brandsAnswer);
        $OtherBrandCount = array_filter($OtherBrandCount);
        $OtherBrandCount = count($OtherBrandCount);
        $brandsAnswer[$brand] = $this->roundingUpValue((3 + (2 / $OtherBrandCount)));
      }
      $this->brands[$brand][$lstoken] = $brandsAnswer[$brand];
    }
  }

  /**
   * Helper method to rounding up the given value.
   * @method roundingUpValue
   *
   * @param  integer $value
   * @param  boolean $force_string
   *    Flag to forcing the decimal point, in string.
   *
   * @return float|string
   */
  private function roundingUpValue($value = 0, $force_string = FALSE) {
    if ($force_string) {
      return number_format($value, self::$decimalPoint, '.', ',');
    }
    return round($value, self::$decimalPoint, PHP_ROUND_HALF_UP);
  }
}
