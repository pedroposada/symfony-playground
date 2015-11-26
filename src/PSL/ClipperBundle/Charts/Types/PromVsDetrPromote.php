<?php
/**
 * Machine Name      = PromVsDetrPromote
 * Slide             = NPS:006
 * Service Name      = clipper.chart.promvsdetrpromote
 * Targeted Question = G002Q001
 */
namespace PSL\ClipperBundle\Charts\Types;

use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\Types\ChartType;

class PromVsDetrPromote extends ChartType {

  private $brands_scores         = array();
  //differences of 'pro' against 'det'
  private $brands_scores_results = array();
  private $answer_set            = array();
  
  // Brand [OTHER]
  // string; "Other" brand label.
  public static $brand_other        = 'Other';
  // boolean|integer; flag to skip "Other" brand, Or int value of NPS.
  public static $ignore_brand_other = FALSE;
  
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
    parent::$decimal_point = 1;
    
    if (self::$ignore_brand_other !== FALSE) {
      $this->brands[] = self::$brand_other;
    }
    
    //create basic structure for @var $this->brands_scores
    $score_set = array_combine(
      array_keys(parent::$net_promoters_cat_range), 
      array_fill(0, count(parent::$net_promoters_cat_range), 
        array(
          'c'   => 0, //count / use for base 
          't'   => 0, //total
          'cal' => 0, //slide calculation / percentage
        )
      )
    );
    $this->brands_scores = array_combine($this->brands, array_fill(0, count($this->brands), $score_set));
    $this->brands_scores_results = array_flip($this->brands);
    
    $this->answer_set = array_combine(array_keys(parent::$net_promoters_cat_range), 
      array_fill(0, count(array_keys(parent::$net_promoters_cat_range)), 0)
    );
    
    if ($event->getCountFiltered()) {
      //extract respondent
      foreach ($event->getData() as $response) {
        //update @var $this->brands_scores
        $this->extractRespondent($response);
      }
      foreach ($this->brands as $index => $brand) {
        //update @var $this->brands_scores
        //update @var $this->brands_scores_results
        $this->calculateBrandScore($brand);
      }
      
      //sort
      arsort($this->brands_scores_results);
    }
    
    //data formation
    $dataTable = array();
    foreach ($this->brands_scores_results as $brand => $result) {
      $data = array('brand' => $brand);
      foreach (parent::$net_promoters_cat_range as $type => $et) {
        $data[$type . 's']       = $this->brands_scores[$brand][$type]['cal'];
        $data[$type . 's_count'] = $this->brands_scores[$brand][$type]['c'];
      }
      $data['diff'] = $result;
      $dataTable[]  = $data;
    }

    // "How much more of my brand do Promoters use compared to Detractors?"
    $event->setTitleLong("How much more of my brand do Promoters use compared to Detractors?");
    
    if (self::$ignore_brand_other !== FALSE) {
      array_pop($this->brands);
    }
    
    return $dataTable;
  }

  /**
   * Method to extract a respondent answer.
   * @method extractRespondent
   *
   * Only account for promoter, passive is ignored.
   *
   * Process will populate
   * - @var $this->brands_scores
   * - @var $this->brands_scores_results
   *
   * Post-format:
   *   $this->brands_scores
   *     BRAND =>
   *       detractor =>
   *         c   => COUNT
   *         t   => TOTAL
   *         cal => no-changes
   *         p   => no-changes
   *       passive =>
   *         c   => COUNT
   *         t   => TOTAL
   *         cal => no-changes
   *         p   => no-changes
   *       promoter =>
   *         c   => COUNT
   *         t   => TOTAL
   *         cal => no-changes
   *         p   => no-changes
   *     ...
   *     
   *     $this->brands_scores_results
   *       BRAND => DIFF-CALC
   *       BRAND => DIFF-CALC
   *       ...
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
    $answers_que  = $this->filterAnswersToQuestionMapViaBrand($answers, 'int');
    
    //filtering answers for promote-scale
    $answers_type = $this->filterAnswersToQuestionMapViaNetPromoter($answers, self::$ignore_brand_other);

    //values assignments
    foreach ($this->brands as $brand) {
      //update brands' scores
      $type = $this->identifyRespondentCategory($answers_type[$brand]);
      $this->brands_scores[$brand][$type]['c']++;
      $this->brands_scores[$brand][$type]['t'] += $answers_que[$brand];
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
   *       detractor =>
   *         c   => no-changes
   *         t   => no-changes
   *         cal => SLIDE-CALC
   *         p   => PERCENTAGE
   *       passive =>
   *         c   => no-changes
   *         t   => no-changes
   *         cal => SLIDE-CALC
   *         p   => PERCENTAGE
   *       promoter =>
   *         c   => no-changes
   *         t   => no-changes
   *         cal => SLIDE-CALC
   *         p   => PERCENTAGE
   *     ...
   *
   * @param  string $brand
   *
   * @return void
   */
  private function calculateBrandScore($brand) {
    extract($this->answer_set);
    $total_count = 0;
    foreach ($this->answer_set as $type => $empty) {
      $count = $this->brands_scores[$brand][$type]['c'];
      $count = max(1, $count);
      $$type = ($this->brands_scores[$brand][$type]['t'] / $count);
      $$type = $this->brands_scores[$brand][$type]['cal'] = $this->roundingUpValue($$type);
      $total_count += $this->brands_scores[$brand][$type]['c'];
    }
    $result = 0;
    if ($detractor) {
      $result = (($promoter / $detractor) * 100);
    }
    $this->brands_scores_results[$brand] = $this->roundingUpValue($result);
  }
}