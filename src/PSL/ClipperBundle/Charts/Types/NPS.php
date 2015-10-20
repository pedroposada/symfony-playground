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
  /**
   * @see ChartType dataTable
   */
  public function dataTable(ChartEvent $event)
  {
    $rows = $dataTable = array();

    // find Detractors, Passives, Promoters and Score per brand
    foreach ($event->getData() as $response) {
      $this->dataRow($event, $response, $rows);
    }
    $rows = $this->explode_tree->explodeTree($rows, "/");

    //prep structure
    $dataTable = array_combine($this->brands, array_fill(0, count($this->brands), array(
      'brand'      => '',
      'base'       => 0,
      'detractors' => 0,
      'passives'   => 0,
      'promoters'  => 0,
      'score'      => 0,
    )));

    array_walk($dataTable, function(&$set, $brand) use (&$rows) {
      $set['brand'] = $brand;
      if (!empty($rows[$brand])) {
        //identify not-aware count
        //All responses who are aware of the brands; $answer > 0
        $not_aware = 0;
        if (!empty($rows[$brand]['detractor'])) {
          $base = array_values($rows[$brand]['detractor']);
          $base = array_filter($base);
          $not_aware = (count($rows[$brand]['detractor']) - count($base));
        }

        //calculation
        $detractor = $passive = $promoter = 0;
        foreach (array('detractor', 'passive', 'promoter') as $type) {
          $$type  = (isset($rows[$brand][$type]) ? count($rows[$brand][$type]) : 0);
        }
        $total = array_sum(array($promoter, $passive, $detractor));

        //update base
        $set['base'] = ($total - $not_aware);

        //formating
        foreach (array('detractor', 'passive', 'promoter') as $type) {
          $plural_var = $type . 's';
          $set[$plural_var] = $this->roundingUpValue((($$type / $total)) * 100);
        }
        $set['score'] = ($set['promoters'] - $set['detractors']);

        unset($rows[$brand]);
      }
    });
    //remove keys
    $dataTable = array_values($dataTable);

    // "Net Promoter Score"
    $event->setTitleLong("Net Promoter Score");

    return $dataTable;
  }

  /**
   * @param $event ChartEvent
   * @param $response LimeSurveyResponse
   * @param &$rows array passed by reference
   */
  private function dataRow(ChartEvent $event, LimeSurveyResponse $response, &$rows)
  {
    // get response for specific respondent
    $answers = $response->getResponseDecoded();

    // extract answers from response array
    $answers = $this->filterAnswersToQuestionMap($answers, 'int');

    // Brand
    //      Type
    //          Token
    //          Token
    //      Type
    //          Token
    foreach ($this->brands as $key => $brand) {
      // determine category
      $category = $this->identifyRespondentCategory($answers[$brand]);
      // set values in rows
      $lstoken = $response->getLsToken();
      $rows["{$brand}/{$category}/{$lstoken}"] = $answers[$brand];
    }
  }
}
