<?php
/**
 * Machine Name      = DoctorPromotingMineAlsoOthersTable
 * Service Name      = clipper.chart.doctor_promoting_mine_also_others_table
 * Targeted Question = G003Q001
 * Targeted Template = ./src/PSL/ClipperBundle/Resources/views/Charts/doctor_promoting_mine_also_others_table.html.twig
 */
namespace PSL\ClipperBundle\Charts\Types;

use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\Types\ChartType;

class DoctorPromotingMineAlsoOthersTable extends ChartType {
  private $map        = array();
  private $qcode      = '';

  private $brands     = array();
  private $respondent = array();

  private $respondent_count = 0;

  private $promoting  = array();
  private $average    = array();

  private static $decimalPoint = 1;

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
    $this->brands = $event->getBrands();
    $this->map    = $this->survey_chart_map->map($event->getSurveyType());
    $this->qcode  = $this->map[$event->getChartType()];

    //extract respondent
    foreach ($event->getData() as $response) {
      //update @var $this->respondent
      //update @var $this->average
      $this->extractRespondent($response);
    }
    $this->respondent_count = count($this->respondent);
    //calculate average, update @var $this->average
    $this->calculateAverage();

    //calculate each brands score
    foreach ($this->brands as $brand) {
      //update @var $this->promoting
      $this->calculateBrandScores($brand);
    }

    //data formation
    $dataTable = array(
      'cols' => array(
        array(
          'label' => 'Brand',
          'type'  => 'string',
        ),
        array(
          'label' => 'Average number of other brands promoted',
          'type'  => 'number',
        ),
        array(
          'label' => 'Most commonly promoted competitor',
          'type'  => 'string',
        ),
        array(
          'label' => '%',
          'type'  => 'string',
        ),
      ),
      'rows' => array(),
    );
    $index = 0;
    $ave_list = array_keys($this->average);
    foreach ($this->promoting as $brand => $score) {
      $dataTable['rows'][] = array(
        'c' => array(
          array('v' => $brand),
          array('v' => $score),
          array('v' => $ave_list[$index]),
          array('v' => $this->average[$ave_list[$index]] . '%'),
        ),
      );
      $index++;
    }

    return $dataTable;
  }

  /**
   * Method to extract a respondent answer.
   * @method extractRespondent
   *
   * Process will populate
   * - @var $this->respondent
   * - @var $this->average
   *
   * Post-format:
   *   $this->respondent
   *     TOKEN
   *       BRAND => ANSWER-VALUE
   *       BRAND => ANSWER-VALUE
   *     TOKEN
   *       BRAND => ANSWER-VALUE
   *       BRAND => ANSWER-VALUE
   *     ...
   *
   *   $this->average
   *     BRAND
   *       RESPONSE-COUNT
   *     BRAND
   *       RESPONSE-COUNT
   *    ...
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
      if (!isset($this->average[$brand])) {
        $this->average[$brand] = 0;
      }
      if (!empty($this->respondent[$lstoken][$brand])) {
        $this->average[$brand]++;
      }
    }
  }

  /**
   * Method to calculate each brand score.
   * @method calculateBrandScores
   *
   * The score doesn't count if respondent votes for the specific brand,
   * but get average in favor of other brands.
   *
   * Process will populate
   * - @var $this->promoting
   *
   * Post-format:
   *   $this->promoting
   *     BRAND
   *       SCORE-VALUE
   *     BRAND
   *       SCORE-VALUE
   *       SCORE-VALUE
   *     ...
   *
   * @param  string $brand
   *
   * @return void
   */
  private function calculateBrandScores($brand) {
    $score = 0;
    foreach ($this->respondent as $token => $brandsAnswer) {
      $promoting = ($brandsAnswer[$brand] > 0 ? 1 : 0);
      $allBrandCount = array_filter($brandsAnswer);
      $allBrandCount = count($allBrandCount);
      $score += ($allBrandCount - $promoting);
    }
    $this->promoting[$brand] = $this->roundingUpValue(($score / $this->respondent_count));
  }


  /**
   * Method to calculate average of each brands, then sort.
   * @method calculateAverage
   *
   * Process will change structure
   * - @var $this->average
   *
   * Post-format:
   *  $this->average
   *    BRAND
   *      AVERAGE-%
   *    BRAND
   *      AVERAGE-%
   *    ...
   *
   * @return void
   */
  private function calculateAverage() {
    foreach ($this->average as $brand => $count) {
      $this->average[$brand] = $this->roundingUpValue((($count / $this->respondent_count) * 100), 0);
    }
    //sort
    arsort($this->average);
  }

  /**
   * Helper method to rounding up the given value.
   * @method roundingUpValue
   *
   * @param  integer $value
   * @param  boolean|int $decPoint
   *    Assign decimal point count, or else @var self::$decimalPoint
   *
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
