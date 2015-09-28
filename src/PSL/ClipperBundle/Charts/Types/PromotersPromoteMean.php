<?php
/**
 * Machine Name      = PromotersPromoteMean
 * Slide             = Extra for Export, Chart 4 / Table 4
 * Service Name      = clipper.chart.promoterspromotemean
 * Targeted Question = G002Q001
 */
namespace PSL\ClipperBundle\Charts\Types;

use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\Types\ChartType;

class PromotersPromoteMean extends ChartType {
  private $respondent       = array();
  private $respondent_count = 0;
  private $promoting        = array();
  private $base             = array();
  
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
    
    //prep promoting & base structure
    $this->promoting = array_combine($this->brands, array_fill(0, count($this->brands), 0));
    $this->base = array_combine($this->brands, array_fill(0, count($this->brands), 0));
    
    //prep results
    $dataTable = array(
      'brands'  => array(),
      'overall' => array(),
    );
    
    if (!empty($event->getCountFiltered())) {
      //extract respondent
      foreach ($event->getData() as $response) {
        //update @var $this->respondent
        $this->extractRespondent($response);
      }
      //count
      $this->respondent_count = count($this->respondent);
      
      //calculate each brands score
      foreach ($this->brands as $brand) {
        //update @var $this->promoting
        //update @var $this->base
        $this->calculateBrandScores($brand);
      }
      
      //sorting
      asort($this->promoting);
    }
    
    //data formation
    foreach ($this->promoting as $brand => $mean) {
      $dataTable['brands'][$brand] = array(
        'base' => $this->base[$brand],
        'mean' => $mean,
      );
    }
    
    $count = max(1, $this->respondent_count);
    $dataTable['overall'] = array(
      'base' => array_sum($this->base),
      'mean' => $this->roundingUpValue((array_sum($this->promoting) / $count)),
    );
        
    return $dataTable;
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
      //respondent overall
      if (!isset($this->respondent[$lstoken])) {
        $this->respondent[$lstoken] = array();
      }
      $this->respondent[$lstoken][$brand] = $answers[$brand];
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
   * - @var $this->base
   *
   * Post-format:
   *   $this->promoting
   *     BRAND = SCORE-VALUE
   *     BRAND = SCORE-VALUE
   *     ...
   *   
   *   $this->base
   *     BRAND = PROMOTER-COUNT
   *     BRAND = PROMOTER-COUNT
   *
   * @param  string $brand
   *
   * @return void
   */
  private function calculateBrandScores($brand) {
    $score = 0;
    foreach ($this->respondent as $token => $brandsAnswer) {
      $otherBrandPromoterCount = 0;
      foreach ($brandsAnswer as $brandAnswer => $answer) {
        $otherBrandPromoterCount += (int) $this->identifyRespondentCategory($answer, 'promoter');          
      }      
      if ($promoting = (int) $this->identifyRespondentCategory($brandsAnswer[$brand], 'promoter')) {
        $this->base[$brand]++;
      }
      $score += ($otherBrandPromoterCount - $promoting);
    }
    $this->promoting[$brand] = $this->roundingUpValue(($score / $this->respondent_count));
  }
}