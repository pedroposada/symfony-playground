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

  private $promoting   = array();
  private $competitors = array();

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

    //prep competitors structure
    $this->competitors = array_combine($this->brands, array_fill(0, count($this->brands), array()));

    //extract respondent
    foreach ($event->getData() as $response) {
      //update @var $this->respondent
      //update @var $this->competitors
      $this->extractRespondent($response);
    }
    $this->respondent_count = count($this->respondent);

    //calculate each brands score
    foreach ($this->brands as $brand) {
      //update @var $this->promoting
      $this->calculateBrandScores($brand);
    }

    //calculate competitors, update @var $this->competitors
    $this->identifyCompetitors();

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
    foreach ($this->promoting as $brand => $score) {
      $dataTable['rows'][] = array(
        'c' => array(
          array('v' => $brand),
          array('v' => $score),
          array('v' => $this->competitors[$brand]['brand']),
          array('v' => $this->competitors[$brand]['perc']),
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
   * - @var $this->respondent
   * - @var $this->competitors
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
   *   $this->competitors
   *     BRAND
   *       BRAND =>
   *         c => TOTAL-COUNT
   *         m => MAX-VOTE
   *       BRAND =>
   *         c => TOTAL-COUNT
   *         m => MAX-VOTE
   *     BRAND
   *       BRAND =>
   *         c => TOTAL-COUNT
   *         m => MAX-VOTE
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
    }

    //get competitor
    foreach ($this->brands as $index => $brand) {
      $respondent_copy = $this->respondent[$lstoken];
      unset($respondent_copy[$brand]); //exclude itself
      arsort($respondent_copy);
      $respondent_copy = array_slice($respondent_copy, 0, 1, TRUE);
      list($competitor_name) = array_keys($respondent_copy);
      if (!isset($this->competitors[$brand][$competitor_name])) {
        $this->competitors[$brand][$competitor_name] = array('c' => 0, 'm' => 0);
      }
      $this->competitors[$brand][$competitor_name]['c']++;
      $this->competitors[$brand][$competitor_name]['m'] = max($this->competitors[$brand][$competitor_name]['m'], $respondent_copy[$competitor_name]);
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
   * Method to select brands competitors by highest answer value.
   * @method identifyCompetitors
   *
   * Process will change structure
   * - @var $this->competitors
   *
   * Post-format:
   *  $this->competitors
   *    BRAND  =>
   *      brand => BRAND-NAME
   *      value => VOTES-PERCENTAGE
   *      perc  => STR-VOTES-PERCENTAGE
   *    BRAND
   *      brand => BRAND-NAME
   *      value => VOTES-PERCENTAGE
   *      perc  => STR-VOTES-PERCENTAGE
   *    ...
   *
   * @return void
   */
  private function identifyCompetitors() {
    foreach ($this->competitors as $brand => $competitors) {
      if ((count($competitors)) == 1) {
        $competitor_max = array_keys($competitors);
        $competitor_max = end($competitor_max);
        $perc = 100;
      }
      else {
        $competitor_max    = array();
        $competitor_counts = 0;
        array_walk($competitors, function($set, $key) use (&$competitor_max, &$competitor_counts) {
          $competitor_max[$key] = $set['m'];
          $competitor_counts += $set['c'];
        });
        arsort($competitor_max);
        $competitor_max = array_slice($competitor_max, 0, 1, TRUE);
        list($competitor_max) = array_keys($competitor_max);
        $perc = (($competitors[$competitor_max]['c'] / $competitor_counts) * 100);
      }
      $this->competitors[$brand] = array(
        'brand' => $competitor_max,
        'value' => $perc,
        'perc'  => $this->roundingUpValue($perc, 0, TRUE) . '%',
      );
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
