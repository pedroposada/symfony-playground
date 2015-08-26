<?php
/**
 * Machine Name      = WhatTheySay
 * Service Name      = clipper.chart.what_they_say
 * Targeted Question = G004Q001, G005Q001, G006Q001, G007Q001, G008Q001, G009Q001
 * Targeted Template = ./src/PSL/ClipperBundle/Resources/views/Charts/what_they_say.html.twig
 */
namespace PSL\ClipperBundle\Charts\Types;

use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\Types\ChartType;

class WhatTheySay extends ChartType {
  private $map    = array();
  private $qcode  = '';
  private $brands = array();

  private $comments = array();

  private static $aDetractorsMax = 6;
  private static $enclosure      = '<span>"</span>';
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
    //prep other attributes
    $this->brands = $event->getBrands();
    $this->map    = $this->survey_chart_map->map($event->getSurveyType());
    $this->qcode  = $this->map[$event->getChartType()];
    $this->qcode2 = $this->map['net_promoters']; //@todo review how to obtain question ID Slide #1

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
        'title'    => "What is {$brand}'s brand DNA?",
        'headings' => array(
          'det' => 'DETRACTORS:',
          'pro' => 'PROMOTERS:',
        ),
        'res'      => array(
          'det' => $this->comments[$brand]['det'],
          'pro' => $this->comments[$brand]['pro'],
        ),
      );
    }

    return $dataTable;
  }

  /**
   * Method to extracts answer.
   * @method extractDetractors
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
   * @return [type]
   */
  private function extractDetractors(LimeSurveyResponse $response) {
    //getting answers
    $answers = $response->getResponseDecoded();

    //filtering answers to which related question; new - support array
    $answers_que = $this->filterAnswersToQuestionMap($answers);
    $answers_que = array_map('trim', $answers_que);

    //filtering answers for promote-scale
    $answers_type = $this->filterAnswersToQuestionMap($answers, $this->qcode2);
    $answers_type = array_map('intval', $answers_type);

    foreach ($this->brands as $brand) {
      $type = (($answers_type[$brand] > self::$aDetractorsMax) ? 'pro' : 'det');
      if ((!empty($answers_que[$brand])) && (count($this->comments[$brand][$type]) <= self::$maxComments)) {
        $this->comments[$brand][$type][] = self::$enclosure . $answers_que[$brand] . self::$enclosure;
      }
    }
  }

  /**
   * Helper method to filter list of answers to given map.
   * @method filterAnswersToQuestionMap
   *
   * @param  array $answers
   *    List of answers, keyed by question index.
   *    Use $response->getResponseDecoded()
   *
   * @param  boolean|string|array $qcode
   *    Question index ID(s)
   *    - FALSE; will use class defined @var $this->qcode
   *    - string/array provide the list.
   *
   * @param  boolean|array $brands
   *    List of brands within the questions.
   *    - FALSE; will use class defined @var $this->brands / @var $event->getBrands()
   *    - array provide the brand list
   *
   * @return array
   *    List of answer assigned to keyed brands
   */
  private function filterAnswersToQuestionMap($answers, $qcode = FALSE, $brands = FALSE) {
    if ((empty($qcode)) && (!empty($this->qcode))) {
      $qcode = $this->qcode;
    }
    if ((empty($brands)) && (!empty($this->brands))) {
      $brands = $this->brands;
    }

    if ((empty($qcode)) || (empty($brands)) || (empty($answers))) {
      return FALSE;
    }

    $answers = array_filter($answers, function($key) use ($qcode) {
      $method = (is_array($qcode) ? 'in_array' : 'strpos');
      return ($method($key, $qcode) !== FALSE);
    }, ARRAY_FILTER_USE_KEY);

    return array_combine($brands, array_values($answers));
  }
}