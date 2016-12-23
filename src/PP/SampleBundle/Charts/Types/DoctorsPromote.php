<?php
/**
 * Machine Name      = DoctorsPromote
 * Slide             = NPS:003
 * Service Name      = clipper.chart.doctorspromote
 * Targeted Question = G003Q001
 */
namespace PP\SampleBundle\Charts\Types;

use PP\SampleBundle\Entity\LimeSurveyResponse;
use PP\SampleBundle\Event\ChartEvent;
use PP\SampleBundle\Charts\Types\ChartType;

class DoctorsPromote extends ChartType {

  private $respondent = array();
  private $promoting  = array();
  private $base = 0;

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
    //prep base structure
    $this->base = 0;
    
    //prep promoting structure
    $this->promoting  = array(
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
    
    if ($event->getCountFiltered()) {
      //extract respondent
      foreach ($event->getData() as $response) {
        //update @var $this->respondent
        $this->extractRespondent($response);
      }
      
      //#final-calculation;
      if (!empty($this->respondent)) {
        foreach ($this->promoting as $ty => $set) {
          $perc = (($set['count'] / $this->base) * 100);
          $this->promoting[$ty]['perc'] = $this->roundingUpValue($perc, 0, FALSE, PHP_ROUND_HALF_DOWN);
        }
      }      
    } // if getCountFiltered()
    
    // "How satisfied is the market?"
    $event->setTitleLong("How satisfied is the market?");
    
    return array(
      'satisfied'    => array(
        'amount' => $this->promoting['sa']['perc'],
        'exclusive' => array('amount' => $this->promoting['se']['perc']),
        'shared'    => array('amount' => $this->promoting['ss']['perc']),
      ),
      'dissatisfied' => array(
        'amount' => $this->promoting['ds']['perc'],
      ),
      'base'         => $this->base,
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
    $answers = $this->filterAnswersToQuestionMapViaBrand($answers, 'int');

    //values assignments
    foreach ($this->brands as $brand) {
      //respondent overall
      if (!isset($this->respondent[$lstoken])) {
        $this->respondent[$lstoken] = array();
      }
      $this->respondent[$lstoken][$brand] = intval($answers[$brand]);
    }
    
    // collect all as base
    $this->base++;
    
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
    $brandsAnswerCat = array();
    foreach ($brandsAnswer as $brand => $answer) {
      $cat = $this->identifyRespondentCategory($answer);
      if (!isset($brandsAnswerCat[$cat])) {
        $brandsAnswerCat[$cat] = 0;
      }
      $brandsAnswerCat[$cat]++;
    }
    
    // Dissatisfied
    if (empty($brandsAnswerCat['promoter'])) {
      $this->promoting['ds']['count']++;
      return;
    }
    
    //Satisfied
    $this->promoting['sa']['count']++;
    
    if ($brandsAnswerCat['promoter'] == 1) {
      // Satisfied (Exclusive)
      $this->promoting['se']['count']++;
      return;
    }
    
    //Satisfied (Shared)
    $this->promoting['ss']['count']++;
    return;
  }
}
