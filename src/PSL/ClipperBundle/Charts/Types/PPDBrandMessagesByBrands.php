<?php
/**
 * Machine Name      = PPDBrandMessagesByBrands
 * Slide             = Extra for Export, Slide X / Table 9-16
 * Service Name      = clipper.chart.ppdbrandmessagesbybrands
 * Targeted Question = G0010Q001, G0011Q001, G0012Q001, G0013Q001, G0014Q001, G0015Q001
 */
namespace PSL\ClipperBundle\Charts\Types;

use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\Types\ChartType;

class PPDBrandMessagesByBrands extends ChartType {
  private $results = array();
  private $questions = array();
  
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
    // copy of PPDBrandMessages for Download: much details data needed.
    $event->setTitleLong('Download: What does my brand represent to Promoters as compared to Detractors by brands?');

    //prep result
    $this->questions = $event->getAttributes();
    $cats = array_combine(array_keys(parent::$net_promoters_cat_range), array_fill(0, count(parent::$net_promoters_cat_range), array(
      'count' => 0, // type-count
      'base'  => 0, // who are aware of the brand & say yes
      'perc'  => 0,
    )));
    $default = array_combine(array_keys($this->questions), array_fill(0, count($this->questions), $cats));
    $this->results = array_combine($this->brands, array_fill(0, count($this->brands), array()));
    array_walk($this->results, function(&$set, $index) use ($default) {
      $set = $default;
    });
    unset($cats, $default);
    
    if (!empty($event->getCountFiltered())) {
      //extract respondent
      foreach ($event->getData() as $response) {
        //update @var $this->results
        $this->extractRespondent($response);
      }
      
      //final calculation
      foreach ($this->results as $brand => $set) {
        $this->calculateScore($this->results[$brand]);
      }
    }
    
    return array(
      'questions' => $this->questions,
      'brands'    => $this->results,
    );
  }
  
  /**
   * Method to extract a respondent answer.
   * @method extractRespondent
   *
   * Process will populate
   * - @var $this->results
   * 
   * Post-format
   *   BRAND-IDNEX => 
   *   
   *     QUESTION-INDEX =>
   *       question => the-question
   *       res => 
   *         detractor =>
   *           count => YES-CAT-COUNT
   *           perc  => no-changes
   *         passive   =>
   *           count => YES-CAT-COUNT
   *           perc  => no-changes
   *         promoter  =>
   *           count => YES-CAT-COUNT
   *           perc  => no-changes
   *
   * @param  LimeSurveyResponse $response
   *
   * @return void
   */
  private function extractRespondent(LimeSurveyResponse $response) {
    //getting answers
    $answers = $response->getResponseDecoded();
    $answersQue = $this->filterAnswersToQuestionMapIntoViaMessages($answers, $this->questions);
    
    //filtering answers for promote-scale
    $answersType = $this->filterAnswersToQuestionMapViaNetPromoter($answers);
    
    foreach ($this->brands as $brand) {
      if (empty($answersQue[$brand])) {
        continue;
      }
      $type = $this->identifyRespondentCategory($answersType[$brand]);
      foreach ($answersQue[$brand] as $qIndex => $qAnswer) {
        $this->results[$brand][$qIndex][$type]['count']++;
        if (!empty($qAnswer)) {
          $this->results[$brand][$qIndex][$type]['base']++;          
        }
      }
    }
  }
  
  /**
   * Calculate percentage for each question in a brand
   * @method calculateScore
   *
   * @param  array $result
   *
   * @return void
   */
  private function calculateScore(&$result) {
    foreach ($result as $ques_index => $ques_set) {
      foreach (parent::$net_promoters_cat_range as $type => $val) {
        if (!empty($ques_set[$type]['count'])) {
          $result[$ques_index][$type]['perc'] = $this->roundingUpValue((($ques_set[$type]['base'] / $ques_set[$type]['count']) * 100));
        }
        unset($result[$ques_index][$type]['count']);
      }
    }
  }
}