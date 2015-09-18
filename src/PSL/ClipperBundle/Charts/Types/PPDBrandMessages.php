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

    //sort & data formation
    $data = array(
      'cols' => array(
        array(
          'label' => 'Brand association question',
          'type'  => 'string',
        ),
        //per-series
      ),
      'rows' => array(),
    );

    //per-series
    $series = array(
      array(
        'label' => 'Detractor',
        'type'  => 'number',
      ),
      array(
        'label' => 'Passive',
        'type'  => 'number',
      ),
      array(
        'label' => 'Promoter',
        'type'  => 'number',
      ),
      array(
        'label' => 'Lowest confidence level',
        'type'  => 'number',
        'p'     => array('role' => 'interval'),
      ),
      array(
        'label' => 'Highest confidence level',
        'type'  => 'number',
        'p'     => array('role' => 'interval'),
      ),
    );
    $series_count = count($series);
    $qcode_count  = count($this->qcode);
    $ques_count   = count($questions);
    for ($i = 0; $i < $qcode_count; $i++) {
      $data['cols'] = array_merge($data['cols'], $series);
    }

    foreach ($this->qcode as $qindex => $qcode) {
      //for sorting
      $key = $this->result[$qcode]['diff'];
      while (isset($data['rows'][$key])) {
        $key += 1;
      }

      //prep
      $data['rows'][$key] = array(
        'c' => array(
          array('v' => $questions[$qindex]),
        ),
      );
      //this-series
      $siri = array(
        array('v' => $this->result[$qcode]['detractor']['perc']),
        array('v' => $this->result[$qcode]['passive']['perc']),
        array('v' => $this->result[$qcode]['promoter']['perc']),
        array('v' => $this->result[$qcode]['confidence']['low']),
        array('v' => $this->result[$qcode]['confidence']['high']),
      );
      //merge
      $empty   = array('v' => '');
      $postfix = $prefix = array();
      if ($qindex > 0) {
        $prefix  = array_fill(0, ($series_count * $qindex), $empty);
      }
      if ($qcode_count > $qindex)  {
        $postfix = array_fill(0, ($series_count * $qcode_count) - ($series_count * ($qindex + 1)), $empty);
      }
      $data['rows'][$key]['c'] = array_merge($data['rows'][$key]['c'], $prefix, $siri, $postfix);
    }
    asort($data['rows']);
    $data['rows'] = array_values($data['rows']);
    return $data;
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
    switch ($go) {
      case 'up':
        $promoter_perc = (1 + $promoter_perc);
        break;

      case 'down':
      default:
        $promoter_perc = (1 - $promoter_perc);
        break;
    }

    $promoter_perc = sqrt($promoter_perc * $promoter_perc);

    if (empty($promoter_perc) || is_nan($promoter_perc)) {
      $promoter_perc = 0;
    }
    else {
      if (!empty($count)) {
        $promoter_perc = ($promoter_perc / $count);
      }
      $promoter_perc = (1.96 * $promoter_perc);
    }

    return $promoter_perc;
  }
}