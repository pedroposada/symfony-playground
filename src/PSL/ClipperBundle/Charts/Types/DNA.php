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
    $answers_que = $this->filterAnswersToQuestionMap($answers, 'trim');

    //filtering answers for promote-scale
    $answers_type = $this->filterAnswersToQuestionMap($answers, 'int', $this->map[parent::$net_promoters]);
    
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
    $key = $this->sanitiveComment($comment);
    
    //reject if same/almost comment entered before    
    if (isset($this->comments[$brand][$type]['comments'][$key])) {
      return;
    }
    
    //OK
    $this->comments[$brand][$type]['comments'][$key] = self::$enclosure . $comment . self::$enclosure;
  }
  
  /**
   * Sanitizes a title, replacing whitespace and a few other characters with dashes.
   * @method sanitiveComment
   * 
   * Limits the output to alphanumeric characters, underscore (_) and dash (-).
   * Whitespace becomes a dash.
   * 
   * Adopted from WordPress sanitize_title_with_dashes()
   *
   * @param  string $title
   *
   * @return string
   */
  private function sanitiveComment($title) {
    $title = strip_tags($title);
    $title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
    $title = str_replace('%', '', $title);
    $title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);
    // if (seems_utf8($title)) {
    //   if (function_exists('mb_strtolower')) {
    //     $title = mb_strtolower($title, 'UTF-8');
    //   }
    //   $title = utf8_uri_encode($title, 200);
    // }
    $title = strtolower($title);
    $title = preg_replace('/&.+?;/', '', $title);
    $title = str_replace('.', '-', $title);
    // if ( 'save' == $context ) {
      $title = str_replace( array( '%c2%a0', '%e2%80%93', '%e2%80%94' ), '-', $title );
      $title = str_replace( array(
        '%c2%a1', '%c2%bf',
        '%c2%ab', '%c2%bb', '%e2%80%b9', '%e2%80%ba',
        '%e2%80%98', '%e2%80%99', '%e2%80%9c', '%e2%80%9d',
        '%e2%80%9a', '%e2%80%9b', '%e2%80%9e', '%e2%80%9f',
        '%c2%a9', '%c2%ae', '%c2%b0', '%e2%80%a6', '%e2%84%a2',
        '%c2%b4', '%cb%8a', '%cc%81', '%cd%81',
        '%cc%80', '%cc%84', '%cc%8c',
      ), '', $title );
      $title = str_replace( '%c3%97', 'x', $title );
    // }
    $title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
    $title = preg_replace('/\s+/', '-', $title);
    $title = preg_replace('|-+|', '-', $title);
    $title = trim($title, '-');
    return $title;
  }
}