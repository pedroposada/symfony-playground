<?php
/**
 * Machine Name      = PromotersPrescribeVersusDetractors
 * Service Name      = clipper.chart.promoters_prescribe_versus_detractors
 * Targeted Question = G002Q001
 * Targeted Template = ./src/PSL/ClipperBundle/Resources/views/Charts/promoters_prescribe_versus_detractors.html.twig
 */
namespace PSL\ClipperBundle\Charts\Types;

use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\Types\ChartType;

class PromotersPrescribeVersusDetractors extends ChartType {
  private $map    = array();
  private $qcode  = '';
  private $qcode2 = '';
  private $brands = array();

  private $brands_scores = array();

  private static $decimalPoint   = 1;
  private static $aDetractorsMax = 6;

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
    $this->qcode2 = $this->map['net_promoters']; //@todo review how to obtain question ID Slide #1

    //create basic structure for @var $this->brands_scores
    $score_set = array(
      'pro' => array( //promoters
        'c' => 0, //count
        't' => 0, //total
      ),
      'det' => array( //detractors
        'c' => 0, //count
        't' => 0, //total
      ),
      'cal' => array( //result
        'pro' => 0, //promoter-value
        'det' => 0, //detractors-value
        'res' => 0, //differences of 'pro' against 'det'
      ),
    );
    $this->brands_scores = array_combine($this->brands, array_fill(0, count($this->brands), $score_set));

    //extract respondent
    foreach ($event->getData() as $response) {
      //update @var $this->brands_scores
      $this->extractRespondent($response);
    }
    foreach ($this->brands as $index => $brand) {
      //update @var $this->brands_scores
      $this->calculateBrandScore($brand);
    }

    //data formation
    $dataTable = array();
    foreach ($this->brands as $index => $brand) {
      $dataTable[$index] = array(
        'title' => "{$brand}: How much more of my brand do Promoters prescribe versus Detractors?",
        'cols'  => array(
          array(
            'label' => "% of market share in each segment",
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
        'rows' => array(),
      );
      foreach (array('det', 'pro') as $type) {
        $dataTable[$index]['rows'][] = array(
          'c' => array(
            array('v' => ($type == 'det' ? 'Detractors' : 'Promoters')),
            array('v' => $this->brands_scores[$brand][$type]['c']),
            array('v' => $this->roundingUpValue($this->brands_scores[$brand]['cal'][$type], FALSE, TRUE) . '%'),
            array('v' => ''), //color will be set on template
          ),
        );
      }
    }
    // FOR DEV ONLY
    if ((php_sapi_name() === 'cli') || (stripos($_SERVER['HTTP_USER_AGENT'], 'curl') !== FALSE)) {
      var_export($this->brands_scores);
      die();
    }
    return $dataTable;
  }

  /**
   * Method to extract a respondent answer.
   * @method extractRespondent
   *
   * Process will populate
   * - @var $this->brands_scores
   *
   * Post-format:
   *   $this->brands_scores
   *     BRAND =>
   *       pro =>
   *         c => COUNT
   *         t => TOTAL
   *       det =>
   *         c => COUNT
   *         t => TOTAL
   *       cal =>
   *         pro => no-changes
   *         det => no-changes
   *         res => no-changes
   *     ...
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
    $answers_que = array_filter($answers, function($key) use ($qcode) {
      return (strpos($key, $qcode) !== FALSE);
    }, ARRAY_FILTER_USE_KEY);
    $answers_que = array_values($answers_que);
    $answers_que = array_map('intval', $answers_que);

    //filtering answers for promote-scale
    $qcode = $this->qcode2; //avoid lexical
    $answers_type = array_filter($answers, function($key) use ($qcode) {
      return (strpos($key, $qcode) !== FALSE);
    }, ARRAY_FILTER_USE_KEY);
    $answers_type = array_values($answers_type);
    $answers_type = array_map('intval', $answers_type);

    //values assignments
    foreach ($this->brands as $index => $brand) {
      //update brands' scores
      $type = (($answers_type[$index] > self::$aDetractorsMax) ? 'pro' : 'det');
      $this->brands_scores[$brand][$type]['c']++;
      $this->brands_scores[$brand][$type]['t'] += $answers_que[$index];
    }
  }

  /**
   * Method to calculate a brand results.
   * @method calculateBrandScore
   *
   * Process will populate
   * - @var $this->brands_scores
   *
   * Post-format:
   *   $this->brands_scores
   *     BRAND =>
   *       pro =>
   *         c => no-changes
   *         t => no-changes
   *       det =>
   *         c => no-changes
   *         t => no-changes
   *       cal =>
   *         pro => PROMOTER-SUM
   *         det => DETRACTORS-SUM
   *         res => RESULT-VALUE
   *     ...
   *
   * @param  string $brand
   *
   * @return void
   */
  private function calculateBrandScore($brand) {
    $pro = $det = 0;
    foreach (array('pro', 'det') as $type) {
      if ($this->brands_scores[$brand][$type]['t'] == 0) {
        $$type = $this->brands_scores[$brand]['cal'][$type] = 0;
      }
      else {
        $base = ($this->brands_scores[$brand][$type]['c'] * 100);
        $$type = $this->brands_scores[$brand]['cal'][$type] = (($this->brands_scores[$brand][$type]['t'] / $base) * 100);
      }
    }
    if (!empty($pro)) {
      $this->brands_scores[$brand]['cal']['res'] = ((($pro - $det) / $det) * 100) * 100;
    }
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