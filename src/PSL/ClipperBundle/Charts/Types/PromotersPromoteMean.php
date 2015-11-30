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
  private $score            = array();
  
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
    // reset : found issue as the method did not destroyed for multiple drill-down: download
    $this->respondent       = array();
    $this->respondent_count = 0;
    $this->promoting        = array();
    $this->base             = array();
    $this->score            = array();
    
    // copy of PromotersPromote for Download: much details data needed.
    $event->setTitleLong("Download: Amongst my promoters which is the most commonly promoted competitor?");

    //prep other attributes
    parent::$decimal_point = 1;
    
    //prep promoting & base structure
    $this->promoting = array_combine($this->brands, array_fill(0, count($this->brands), 0));
    $this->base = array_combine($this->brands, array_fill(0, count($this->brands), 0));
    $this->score = array_combine($this->brands, array_fill(0, count($this->brands), 0));
    
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
        'mean' => $this->roundingUpValue($mean),
      );
    }
    
    $dataTable['overall'] = array(
      'base' => $this->respondent_count,
      'mean' => $this->roundingUpValue((array_sum($this->promoting) / count($this->promoting))),
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
    $answers = $this->filterAnswersToQuestionMapViaBrand($answers, 'int');

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
    foreach ($this->respondent as $token => $brandsAnswer) {
      if (!$this->validateRespondentCategory($brandsAnswer[$brand], 'promoter')) {
        continue;
      }
      $this->base[$brand]++;
      foreach ($brandsAnswer as $ans_brand => $answer) {
        if ($ans_brand != $brand) {
          $this->score[$brand] += (int) $this->validateRespondentCategory($answer, 'promoter');
        }
      } // foreach answer
    } // foreach responses
    if (!empty($this->base[$brand])) {
      $this->promoting[$brand] = ($this->score[$brand] / $this->base[$brand]);
      $this->promoting[$brand] = $this->promoting[$brand];
    }
  }
}