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
  private $comments   = array();

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
    $this->comments = array_combine(
      array_keys(parent::$net_promoters_cat_range), 
      array_fill(0, count(parent::$net_promoters_cat_range), array(        
        'comments' => array(),
      ))
    );
    $this->comments = array_combine(
      $this->brands, 
      array_fill(0, count($this->brands), $this->comments)
    );
    //prep other attributes
    parent::$decimal_point = 1;
    
    //extract comments from respondent
    foreach ($event->getData() as $response) {
      //update @var $this->comments
      $this->extractDetractors($response);
    }

    //data formation
    $dataTable = array();

    foreach ($this->brands as $brand) {
      $data = array(
        'brand' => $brand
      );
      foreach (parent::$net_promoters_cat_range as $type => $set) {
        $comments = array_values($this->comments[$brand][$type]['comments']);
        $data[$type . 's'] = $comments;
      }
      unset($this->comments[$brand]);
      $dataTable[] = $data;
    }

    $event->setTitleLong("What does my brand represent to Promoters as compared to Detractors?");

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
   *       detractor =>
   *         comments =>
   *           COMMENT
   *           COMMENT
   *       passive  =>
   *         comments =>
   *       promoter =>
   *         comments =>
   *           COMMENT
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
    $answers_que = $this->filterAnswersToQuestionMapViaBrand($answers, 'trim');

    //filtering answers for promote-scale
    $answers_type = $this->filterAnswersToQuestionMapViaNetPromoter($answers);
    
    foreach ($this->brands as $brand) {
      $type = $this->identifyRespondentCategory($answers_type[$brand]);
      $this->addComment($brand, $type, $answers_que[$brand]);
    }
  }
  
  /**
   * Method to register comment.
   * @method addComment
   *
   * @param  string $brand
   *    Brand name.
   *    
   * @param  string $type
   *    Category name.
   *    
   * @param  string $comment
   */
  private function addComment($brand, $type, $comment = '') {
    //reject if empty
    if (empty($comment)) {
      return;
    }
    //reject if max-up
    if (count($this->comments[$brand][$type]['comments']) > self::$maxComments) {
      return;
    }
    
    //identify
    $key = $this->sanitizeComment($comment);
    
    //reject if same/almost comment entered before    
    if (isset($this->comments[$brand][$type]['comments'][$key])) {
      return;
    }
    
    //OK
    $this->comments[$brand][$type]['comments'][$key] = self::$enclosure . $comment . self::$enclosure;
  }
}