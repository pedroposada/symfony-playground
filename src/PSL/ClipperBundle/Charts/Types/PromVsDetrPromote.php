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
  private $brands_scores_results = array();

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

    //create basic structure for @var $this->brands_scores
    $score_set = array(
      'pro' => array( //promoters
        'c' => 0, //count
        't' => 0, //total
      ),
      'det' => array( //detractors
        'c' => 0, //count
        't' => 0, //total
      ),
      'cal' => array( //result
        'pro' => 0, //promoter-value
        'det' => 0, //detractors-value
        'res' => 0, //differences of 'pro' against 'det'
      ),
    );
    $this->brands_scores = array_combine($this->brands, array_fill(0, count($this->brands), $score_set));
    $this->brands_scores_results = array_flip($this->brands);
    
    if ($event->getCountFiltered()) {
      //extract respondent
      foreach ($event->getData() as $response) {
        //update @var $this->brands_scores
        $this->extractRespondent($response);
      }
      foreach ($this->brands as $index => $brand) {
        //update @var $this->brands_scores
        $this->calculateBrandScore($brand);
      }
      
      //sort
      arsort($this->brands_scores_results);
    }
    
    //data formation
    $dataTable = array();
    foreach ($this->brands_scores_results as $brand => $result) {
      $dataTable[] = array(
        'brand'      => $brand,
        'promoters'  => $this->brands_scores[$brand]['pro']['c'],
        'detractors' => $this->brands_scores[$brand]['det']['c'],
        'diff'       => $this->brands_scores[$brand]['cal']['res'],
      );
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
   *
   * Post-format:
   *   $this->brands_scores
   *     BRAND =>
   *       pro =>
   *         c => COUNT
   *         t => TOTAL
   *       det =>
   *         c => COUNT
   *         t => TOTAL
   *       cal =>
   *         pro => no-changes
   *         det => no-changes
   *         res => no-changes
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
    //filtering answers to which related question
    $answers_que  = $this->filterAnswersToQuestionMap($answers, 'int');
    //filtering answers for promote-scale
    $answers_type = $this->filterAnswersToQuestionMap($answers, 'int', $this->map[parent::$net_promoters]);

    //values assignments
    foreach ($this->brands as $brand) {
      //update brands' scores
      $type = $this->identifyRespondentCategory($answers_type[$brand]);
      $type = array_search($type, array('detractor', 'promoter'), TRUE);
      if ($type === FALSE) {
        //ignore passive
        continue; //foreach
      }
      $type = (empty($type) ? 'det' : 'pro');
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
   *       pro =>
   *         c => no-changes
   *         t => no-changes
   *       det =>
   *         c => no-changes
   *         t => no-changes
   *       cal =>
   *         pro => PROMOTER-SUM
   *         det => DETRACTORS-SUM
   *         res => RESULT-VALUE
   *     ...
   *
   * @param  string $brand
   *
   * @return void
   */
  private function calculateBrandScore($brand) {
    $pro = $det = 0;
    foreach (array('pro', 'det') as $type) {
      if ($this->brands_scores[$brand][$type]['t'] == 0) {
        $$type = $this->brands_scores[$brand]['cal'][$type] = 0;
      }
      else {
        $base = ($this->brands_scores[$brand][$type]['c'] * 100);
        $$type = $this->brands_scores[$brand]['cal'][$type] = (($this->brands_scores[$brand][$type]['t'] / $base) * 100);
      }
    }
    $result = ($pro - $det);
    if (!empty($det)) {
      $result = ($result / $det);
    }
    $result *= 0.1;
    $result *= 0.1;
    $this->brands_scores[$brand]['cal']['res'] = $this->brands_scores_results[$brand] = $result;
  }
}