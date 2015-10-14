<?php
/**
 * Machine Name      = PPDBrandMessages
 * Slide             = NPS:007
 * Service Name      = clipper.chart.ppdbrandmessages
 * Targeted Question = G0010Q001, G0011Q001, G0012Q001, G0013Q001, G0014Q001, G0015Q001
 */
namespace PSL\ClipperBundle\Charts\Types;

use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\Types\ChartType;

class PPDBrandMessages extends ChartType {

  private $result = array();
  private $counts = array();

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
    //prep result structure
    $dataTable = array();
    
    //stop if no responses
    if (empty($event->getCountFiltered())) {
      return $dataTable;
    }
    
    //prep calculation structure
    $set = array_keys(parent::$net_promoters_cat_range);
    $set = array_flip($set);
    array_walk($set, function(&$value, $key) {
      $value = array('count' => 0);
    });
    $this->result = array_combine($this->qcode, array_fill(0, count($this->qcode), $set));
    //prep counts structure
    $this->counts = array_combine(array_keys(parent::$net_promoters_cat_range), $set);
    

    //get set of question
    $questions = $event->getAttributes();

    //extract respondent
    foreach ($event->getData() as $response) {
      //update @var $this->result
      //update @var $this->counts
      $this->extractRespondent($response);
    }

    //final calculation
    foreach ($this->qcode as $qindex => $qcode) {
      foreach ($this->counts as $type => $info) {
        if (empty($this->result[$qcode][$type]['count'])) {
          $this->result[$qcode][$type]['perc'] = 0;
          continue;  
        }
        $this->result[$qcode][$type]['perc'] = (($this->result[$qcode][$type]['count'] / $info['count']) * 100);
        $this->result[$qcode][$type]['perc'] = $this->roundingUpValue($this->result[$qcode][$type]['perc']);
      }
      $this->result[$qcode]['diff'] = ($this->result[$qcode]['promoter']['perc'] - $this->result[$qcode]['detractor']['perc']);
      $this->result[$qcode]['diff'] = $this->roundingUpValue($this->result[$qcode]['diff'], 0);
      $this->result[$qcode]['confidence'] = array(
        'low'  => $this->calculateConfidenceInterval($this->result[$qcode]['promoter']['perc'], 'down', $this->counts['promoter']['count']),
        'high' => $this->calculateConfidenceInterval($this->result[$qcode]['promoter']['perc'], 'up', $this->counts['promoter']['count']),
      );
    }
    
    //formatting
    foreach ($this->qcode as $qindex => $qcode) {
      //for sorting; abstract key
      $key = $this->result[$qcode]['diff'];
      while (isset($dataTable[$key])) {
        $key += 1;
      }
      $dataTable[$key] = array(
        'message'    => $questions[$qindex],
        'detractors' => $this->result[$qcode]['detractor']['perc'],
        'passives'   => $this->result[$qcode]['passive']['perc'],
        'promoters'  => $this->result[$qcode]['promoter']['perc'],
        'lcl'        => $this->result[$qcode]['confidence']['low'],
        'hcl'        => $this->result[$qcode]['confidence']['high'],
      );
      unset($questions[$qindex]);
      unset($this->result[$qcode]);
    }
    
    //sort by abstract key
    asort($dataTable);
    $dataTable = array_values($dataTable);
    
    return $dataTable;
  }

  /**
   * Method to extract a respondent answer.
   * @method extractRespondent
   *
   * Process will populate
   * - @var $this->result
   *     QUESTION-ID =>
   *        detractor =>
   *           count => QUESTION-CAT-COUNT
   *        passive =>
   *           count => QUESTION-CAT-COUNT
   *        promoter =>
   *           count => QUESTION-CAT-COUNT
   *     ...
   *
   * - @var $this->counts
   *     detractor =>
   *        count => QUESTION-CAT-COUNT-ALL
   *     passive =>
   *        count => QUESTION-CAT-COUNT-ALL
   *     promoter =>
   *        count => QUESTION-CAT-COUNT-ALL
   *
   * Post-format
   *
   * @param  LimeSurveyResponse $response
   *
   * @return void
   */
  private function extractRespondent(LimeSurveyResponse $response) {
    //getting answers
    $answers = $response->getResponseDecoded();
    $answers_que = $this->filterAnswersToQuestionMap($answers, 'y/n');

    //filtering answers for promote-scale
    $answers_type = $this->filterAnswersToQuestionMap($answers, 'int', $this->map[parent::$net_promoters]);

    foreach ($this->brands as $brand) {
      $type = $this->identifyRespondentCategory($answers_type[$brand]);
      $this->counts[$type]['count']++;
      foreach ($this->qcode as $qindex => $qcode) {
        $this->result[$qcode][$type]['count'] += $answers_que[$brand][$qindex];
      }
    }
  }

  /**
   * Method to calculate a Confidence Interval.
   * @method calculateConfidenceInterval
   *
   * @param  float $promoter_perc
   *    Promoter percentage.
   *
   * @param  string $go
   *    up   => high
   *    down => low
   *
   * @param  integer $count
   *    Promoter count.
   *
   * @return float
   */
  private function calculateConfidenceInterval($promoter_perc, $go = 'down', $count = 0) {
    $pow = sqrt($promoter_perc);
    switch ($go) {
      case 'up':
        $promoter_go = ($pow + $promoter_perc);
        break;

      case 'down':
      default:
        $promoter_go = ($pow - $promoter_perc);
        break;
    }
    $count = max(1, $count);
    $cal = (($promoter_perc * $promoter_go) / $count);
    $cal = abs($cal);
    $cal = sqrt($cal);
    $promoter_perc = $pow + (1.96 * $cal);       
    return $this->roundingUpValue($promoter_perc);
  }
}