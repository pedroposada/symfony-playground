<?php
/**
 * Machine Name      = DetractorsPromote
 * Slide             = NPS:005
 * Service Name      = clipper.chart.detractorspromote
 * Targeted Question = G003Q001
 */
namespace PSL\ClipperBundle\Charts\Types;

use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\Types\ChartType;

class DetractorsPromote extends ChartType {

  private $competitors = array();
  
  //All responses who are detractors of the brands
  // $answer > 0
  private $base        = array();

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
    //prep structure
    $dataTable = array();
        
    //prep other attributes
    parent::$decimal_point = 1;

    //prep competitors structure
    $this->competitors = array_combine($this->brands, array_fill(0, count($this->brands), array()));
    
    //prep base structure
    $this->base = array_combine($this->brands, array_fill(0, count($this->brands), 0));
    
    //stop if no responses
    if ($event->getCountFiltered()) {
      //extract respondent
      foreach ($event->getData() as $response) {
        //update @var $this->competitors
        $this->extractRespondent($response);
      }
      
      //calculate each brands score    
      foreach ($this->brands as $brand) {
        //calculate competitors, update @var $this->competitors
        $this->calculateCompetitors($brand);
      }
    } //if getCountFiltered()

    //data formation
    foreach ($this->brands as $brand) {
      $dataTable[] = array(
        'brand'       => $brand,
        'base'        => $this->base[$brand],
        'competitors' => (empty($this->competitors[$brand]) ? new \stdClass() : $this->competitors[$brand]),
      );
    }

    $event->setTitleLong('Amongst my Detractors, which other brands do they promote?');

    return $dataTable;
  }

  /**
   * Method to extract a respondent answer.
   * @method extractRespondent
   *
   * Process will populate
   * - @var $this->competitors
   *
   *   $this->competitors
   *     BRAND
   *       BRAND => PROMOTER-COUNT
   *       BRAND => PROMOTER-COUNT
   *     BRAND
   *       BRAND => PROMOTER-COUNT
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
    $answers = $this->filterAnswersToQuestionMapViaBrand($answers, 'int');    
    
    //capture base
    foreach ($this->brands as $brand) {
      if ($this->validateRespondentCategory($answers[$brand], 'detractor')) {
        $this->extractPromoter($brand, $answers);        
      }
    }
  }
  
  /**
   * Method to get a Promoters promotes.
   * @method extractPromoter
   *
   * @param  string $brand
   * @param  array $answers
   *
   * @return void
   */
  private function extractPromoter($brand, $answers) {
    $this->base[$brand]++;
    foreach ($answers as $ans_brnd => $answer) {
      if (($ans_brnd == $brand) || (empty($answer))) {
        continue;
      }
      if ($this->validateRespondentCategory($answer, 'promoter')) {
        if (!isset($this->competitors[$brand][$ans_brnd])) {
          $this->competitors[$brand][$ans_brnd] = 0;          
        }
        $this->competitors[$brand][$ans_brnd]++;
      }
    }
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
   *      BRAND-NAME => VOTES-PERCENTAGE
   *      BRAND-NAME => VOTES-PERCENTAGE
   *    BRAND  =>
   *      BRAND-NAME => VOTES-PERCENTAGE
   *    ...
   * @param  string $brand
   * 
   * @return void
   */
  private function calculateCompetitors($brand) {
    if (empty($this->competitors[$brand])) {
      return;
    }
    $competitors_count = $this->base[$brand];
    array_walk($this->competitors[$brand], function(&$count, $comp_brand) use ($competitors_count) {
      $count = $this->roundingUpValue((($count / $competitors_count) * 100), 0);
    });
  }
}
