<?php
/**
 * Machine Name      = NPS
 * Slide             = NPS:001
 * Service Name      = clipper.chart.nps
 * Targeted Question = G003Q001
 */
namespace PSL\ClipperBundle\Charts\Types;

use Doctrine\Common\Collections\ArrayCollection;

use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\Types\ChartType;

class NPS extends ChartType
{
  private $respondent = array();
  //All responses who are aware of the brands
  // $answer > 0
  private $base       = array();
  private $brands_results;
  private $dataTable_data;
  
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
  public function dataTable(ChartEvent $event)
  {
    //prep other attributes
    parent::$decimal_point = 2;

    //prep brands_results structure
    $this->brands_results = array_combine($this->brands, array_fill(0, count($this->brands), array()));
    
    //prep base structure
    $this->base = array_combine($this->brands, array_fill(0, count($this->brands), 0));
    
    //prep structure
    $this->dataTable_data = array_combine($this->brands, array_fill(0, count($this->brands), array(
      'brand'      => '',
      'base'       => 0,
      'detractors' => 0,
      'passives'   => 0,
      'promoters'  => 0,
      'score'      => 0,
    )));
    
    // "Net Promoter Score"
    $event->setTitleLong("Net Promoter Score");

    //STOP if no responses
    if (empty($event->getCountFiltered())) {
      return $this->dataTable_data;
    }

    //extract respondent
    foreach ($event->getData() as $response) {
      //update @var $this->brands_results
      $this->extractRespondent($response);
    }
    
    $score_order = array();
    $overall_avg = $overall_total = $overall_count = 0;
    if (!empty($this->respondent)) {
      //#final-calculation
      foreach ($this->brands_results as $brand => $respondent) {
        $this->dataTable_data[$brand]['brand'] = $brand;
        $this->dataTable_data[$brand]['base']  = $this->base[$brand];
        foreach (array('detractors', 'passives', 'promoters') as $type) {
          if (!empty($this->dataTable_data[$brand][$type])) {
            $this->dataTable_data[$brand][$type] = (($this->dataTable_data[$brand][$type] / $this->base[$brand]) * 100);
          }
        }
        $score = $this->dataTable_data[$brand]['score'] = $this->dataTable_data[$brand]['promoters'] - $this->dataTable_data[$brand]['detractors'];
        while (isset($score_order[$score])) {
          $score++;
        }
        $score_order[$score] = $brand;
        // rounding up last
        $this->dataTable_data[$brand]['score'] = $this->roundingUpValue($this->dataTable_data[$brand]['score'], 0, FALSE, PHP_ROUND_HALF_DOWN);
        foreach (array('detractors', 'passives', 'promoters') as $type) {
          $this->dataTable_data[$brand][$type] = $this->roundingUpValue($this->dataTable_data[$brand][$type], 0, FALSE, PHP_ROUND_HALF_DOWN);
        }
      }
    }
    $this->respondent = array();
    
    // order descending by score
    $dataTable_data_bk = $this->dataTable_data;
    $this->dataTable_data = array();
    krsort($score_order);    
    foreach ($score_order as $brand_odr) {
      $this->dataTable_data[$brand_odr] = $dataTable_data_bk[$brand_odr];
    }
    unset($dataTable_data_bk, $score_order);
    
    //remove keys
    $this->dataTable_data = array_values($this->dataTable_data);

    return $this->dataTable_data;
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
      //brands overall
      if (!isset($this->brands_results[$brand][$lstoken])) {
        $this->brands_results[$brand][$lstoken] = 0;
      }
      //respondent overall
      if (!isset($this->respondent[$lstoken])) {
        $this->respondent[$lstoken] = array();
      }
      if (!is_null($answers[$brand])) {
        $this->respondent[$lstoken][$brand] = intval($answers[$brand]);
        //capture size
        $type = $this->identifyRespondentCategory($answers[$brand]);
        $this->dataTable_data[$brand]["{$type}s"]++;
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
          $brandsAnswer[$brand] = (3 + (2 / $OtherBrandCount));
          break;
      } //switch
      $this->brands_results[$brand][$lstoken] = $brandsAnswer[$brand];
    } //foreach
  }
}
