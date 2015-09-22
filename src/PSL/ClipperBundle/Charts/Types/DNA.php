<?php
/**
 * Machine Name      = DNA
 * Slide             = NPS:009
 * Service Name      = clipper.chart.dna
 * Targeted Question = G004Q001, G005Q001, G006Q001, G007Q001, G008Q001, G009Q001
 */
namespace PSL\ClipperBundle\Charts\Types;

use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\Types\ChartType;

class DNA extends ChartType {
  private $comments = array();

  private static $enclosure      = '';
  private static $maxComments    = 15;

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
    //prep comments structure
    $this->comments = array_combine($this->brands, array_fill(0, count($this->brands), array('det' => array(), 'pro' => array())));

    //extract comments from respondent
    foreach ($event->getData() as $response) {
      //update @var $this->comments
      $this->extractDetractors($response);
    }

    //data formation
    $dataTable = array();

    foreach ($this->brands as $brand) {
      $dataTable[] = array(
        'brand'      => $brand,
        'detractors' => $this->comments[$brand]['det'],
        'promoters'  => $this->comments[$brand]['pro'],
      );
      unset($this->comments[$brand]);
    }

    return $dataTable;
  }

  /**
   * Method to extracts answer.
   * @method extractDetractors
   *
   * Only account for promoter, passive is ignored.
   *
   * Process will populate
   * - @var $this->comments
   *
   * Post-format:
   *   $this->comments
   *     BRAND =>
   *       pro =>
   *         COMMENT
   *         COMMENT
   *       det =>
   *         COMMENT
   *     ...
   *
   * @param  LimeSurveyResponse $response
   *
   * @return void
   */
  private function extractDetractors(LimeSurveyResponse $response) {
    //getting answers
    $answers = $response->getResponseDecoded();

    //filtering answers to which related question; new - support array
    $answers_que = $this->filterAnswersToQuestionMap($answers, 'trim');

    //filtering answers for promote-scale
    $answers_type = $this->filterAnswersToQuestionMap($answers, 'int', $this->map[parent::$net_promoters]);

    foreach ($this->brands as $brand) {
      $type = $this->identifyRespondentCategory($answers_type[$brand]);
      $type = array_search($type, array('detractor', 'promoter'), TRUE);
      if ($type === FALSE) {
        //ignore passive
        continue; //foreach
      }
      $type = (empty($type) ? 'det' : 'pro');
      if ((!empty($answers_que[$brand])) && (count($this->comments[$brand][$type]) <= self::$maxComments)) {
        $this->comments[$brand][$type][] = self::$enclosure . $answers_que[$brand] . self::$enclosure;
      }
    }
  }
}