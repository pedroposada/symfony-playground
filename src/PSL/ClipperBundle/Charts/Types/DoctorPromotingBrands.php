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

  private $respondent = array();

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
    //extract respondent
    foreach ($event->getData() as $response) {
      //update @var $this->respondent
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
