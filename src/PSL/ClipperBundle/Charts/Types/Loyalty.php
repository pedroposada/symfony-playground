<?php
/**
 * Machine Name      = Loyalty
 * Slide             = NPS:002
 * Service Name      = clipper.chart.loyalty
 * Targeted Question = G003Q001
 */
namespace PSL\ClipperBundle\Charts\Types;

use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\Types\ChartType;

class Loyalty extends ChartType {
  private $respondent = array();
  private $brands_results;

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
    parent::$decimal_point = 2;

    //prep brands_results structure
    $this->brands_results = array_combine($this->brands, array_fill(0, count($this->brands), array()));

    //extract respondent
    foreach ($event->getData() as $response) {
      //update @var $this->brands_results
      $this->extractRespondent($response);
    }
    
    $overall_avg = $overall_total = $overall_count = 0;
    if (!empty($this->respondent)) {
      //#final-calculation
      foreach ($this->brands_results as $brand => $respondent) {
        $total = array_sum($respondent);
        $overall_total += $total;
        $count = count($respondent);
        $overall_count += $count;
        $this->brands_results[$brand] = $this->roundingUpValue(($total / $count));
      }
      $overall_avg = $this->roundingUpValue(($overall_total / $overall_count));
    }

    //sorting
    arsort($this->brands_results);

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
    foreach ($this->brands_results as $brand => $loyalty) {
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
   * - @var $this->brands_results
   * - @var $this->respondent
   *
   *
   * Post-format
   *   $this->brands_results
   *     BRAND
   *       TOKEN => SCORE
   *       TOKEN => SCORE
   *     BRAND
   *       TOKEN => SCORE
   *     ...
   *
   * Note: This format will change once at #final-calculation
   *   $this->brands_results
   *     BRAND => SCORE
   *     BRAND => SCORE
   *     ...
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
   * @param  LimeSurveyResponse $response
   *
   * @return void
   */
  private function extractRespondent(LimeSurveyResponse $response) {
    //getting respondent token
    $lstoken = $response->getLsToken();

    //getting answers
    $answers = $response->getResponseDecoded();
    $answers = $this->filterAnswersToQuestionMap($answers, 'int');

    //values assignments
    foreach ($this->brands as $brand) {
      //brands overall
      if (!isset($this->brands_results[$brand][$lstoken])) {
        $this->brands_results[$brand][$lstoken] = 0;
      }
      //respondent overall
      if (!isset($this->respondent[$lstoken])) {
        $this->respondent[$lstoken] = array();
      }
      $this->respondent[$lstoken][$brand] = $answers[$brand];
    }

    //convert brand into score by each respondent
    $this->calculateRespondentScore($lstoken, $this->respondent[$lstoken]);
  }

  /**
   * Method to determine Brands Score.
   * @method calculateRespondentScore
   *
   * This process will change value for;
   * - @var $this->brands_results, based on respondent answers
   * - @see  parent->identifyRespondentCategory()
   *
   * @param  string $lstoken
   * @param  array $brandsAnswer
   *
   * @return void
   */
  private function calculateRespondentScore($lstoken, $brandsAnswer = array()) {
    foreach ($brandsAnswer as $brand => $answer) {
      $brandsAnswer[$brand] = 0;
      switch ($this->identifyRespondentCategory($answer)) {
        case 'detractor':
          $brandsAnswer[$brand] = 1;
          break;

        case 'passive':
          $brandsAnswer[$brand] = 2;
          break;

        case 'promoter':
          $OtherBrandCount = array_filter($brandsAnswer);
          $OtherBrandCount = count($OtherBrandCount);
          $brandsAnswer[$brand] = $this->roundingUpValue((3 + (2 / $OtherBrandCount)));
          break;
      } //switch
      $this->brands_results[$brand][$lstoken] = $brandsAnswer[$brand];
    } //foreach
  }
}
