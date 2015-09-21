<?php
/**
 * Machine Name      = DoctorsPromote
 * Slide             = NPS:003
 * Service Name      = clipper.chart.doctorspromote
 * Targeted Question = G003Q001
 */
namespace PSL\ClipperBundle\Charts\Types;

use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\Types\ChartType;

class DoctorsPromote extends ChartType {

  private $respondent = array();

  private $promoting  = array(
    'ds' => array(  //Dissatisfied
      'count'  => 0,
      'perc'   => 0,
    ),
    'sa' => array(  //Satisfied
      'count'  => 0,
      'perc'   => 0,
    ),
    'se' => array(  //Satisfied (Exclusive)
      'count'  => 0,
      'perc'   => 0,
    ),
    'ss' => array(  //Satisfied (Shared)
      'count'  => 0,
      'perc'   => 0,
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
    if ($event->getCountFiltered()) {
      //extract respondent
      foreach ($event->getData() as $response) {
        //update @var $this->respondent
        $this->extractRespondent($response);
      }

      //#final-calculation; calculate the aggregated count into parentage
      $total = $this->promoting['ds']['count'] + $this->promoting['sa']['count'];
      if (!empty($this->respondent)) {
        foreach ($this->promoting as $ty => $set) {
          $this->promoting[$ty]['perc'] = $this->roundingUpValue(($set['count'] / $total));
        }
      }
    } // if getCountFiltered()

    return array(
      'satisfied'    => array(
        'amount' => $this->promoting['sa']['perc'],
        'exclusive' => array('amount' => $this->promoting['se']['perc']),
        'shared'    => array('amount' => $this->promoting['ss']['perc']),
      ),
      'dissatisfied' => array(
        'amount' => $this->promoting['ds']['perc'],
      ),
    );
  }

  /**
   * Method to extract a respondent answer.
   * @method extractRespondent
   *
   * Process will populate
   * - @var $this->respondent
   *
   * Post-format:
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
    $answers = $this->filterAnswersToQuestionMap($answers, 'int');

    //values assignments
    foreach ($this->brands as $brand) {
      //respondent overall
      if (!isset($this->respondent[$lstoken])) {
        $this->respondent[$lstoken] = array();
      }
      $this->respondent[$lstoken][$brand] = $answers[$brand];
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
}
