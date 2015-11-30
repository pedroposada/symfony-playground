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
  //All responses who are aware of the brands
  // $answer > 0
  private $base       = array();
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
    
    //prep base structure
    $this->base = array_combine($this->brands, array_fill(0, count($this->brands), 0));
    
    //prep structure
    $dataTable = array('mean' => 0, 'base' => 0, 'brands' => array());
    
    // "How loyal are doctors to my brand?"
    $event->setTitleLong("How loyal are doctors to my brand?");
    
    //STOP if no responses
    if (empty($event->getCountFiltered())) {
      return $dataTable;
    }

    //extract respondent
    $respondent_count = 0;
    foreach ($event->getData() as $response) {
      //update @var $this->brands_results
      $this->extractRespondent($response);
      $respondent_count++;
    }
    
    $all_res_count = $all_res_total = 0;
    if (!empty($respondent_count)) {
      //#final-calculation: loyalty
     foreach ($this->brands_results as $brand => $respondent) {
        if (empty($respondent)) {
          $this->brands_results[$brand] = 0;
          continue;
        }
        if (!empty($this->base[$brand])) {
          $all_res_total += $total = array_sum($respondent);
          $all_res_count += $count = count($respondent);
          $this->brands_results[$brand] = $this->roundingUpValue(($total / $count), FALSE, FALSE, PHP_ROUND_HALF_DOWN);          
        } else {
          $this->brands_results[$brand] = 0;
        }
      }
    }
    
    //sorting by avg ASC
    arsort($this->brands_results);
    
    $dataTable['base'] = $respondent_count;
    $dataTable['mean'] = $this->roundingUpValue(($all_res_total / $all_res_count), FALSE, FALSE, PHP_ROUND_HALF_DOWN);
    foreach ($this->brands_results as $brand => $loyalty) {
      $dataTable['brands'][] = array(
        'brand'   => $brand,
        'base'    => $this->base[$brand],
        'loyalty' => $loyalty,
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
    $answers = $this->filterAnswersToQuestionMapViaBrand($answers, 'int');

    //values assignments
    foreach ($this->brands as $brand) {
      //respondent overall
      if (!isset($this->respondent[$lstoken])) {
        $this->respondent[$lstoken] = array();
      }
      $this->respondent[$lstoken][$brand] = intval($answers[$brand]);
      //capture answer
      if (!is_null($answers[$brand])) {
        //capture base
        $this->base[$brand]++;
      }
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
    // get promoting brand count first
    $promoting_others = 0;
    foreach ($brandsAnswer as $brand => $answer) {
      if ($this->validateRespondentCategory($answer, 'promoter')) {
        $promoting_others++;        
      }
    }
    
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
          $pregsw = 2;
          if ($promoting_others) {
            $pregsw = ($pregsw / $promoting_others);
          }
          $brandsAnswer[$brand] = (3 + $pregsw);
          break;
      } //switch
      $this->brands_results[$brand][$lstoken] = $brandsAnswer[$brand];
    } //foreach
  }
}
