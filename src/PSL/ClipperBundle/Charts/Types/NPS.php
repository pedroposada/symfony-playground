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
    $dataTable = array();
    $rows = array();

    // find Detractors, Passives, Promoters and Score per brand
    foreach ($event->getData() as $response) {
      $this->dataRow($event, $response, $rows);
    }
    $tree = $this->explode_tree->explodeTree($rows, "/");

    $dataTable['cols'] = array(
      array(
        'id' => 'b',
        'label' => 'Brand',
        'type' => 'string',
      ),
      array(
        'id' => 'P',
        'label' => 'Promoters',
        'type' => 'number',
      ),
      array(
        'id' => 'a',
        'label' => 'Passives',
        'type' => 'number',
      ),
      array(
        'id' => 'd',
        'label' => 'Detractors',
        'type' => 'number',
      ),
      array(
        'id' => 's',
        'label' => 'Score',
        'type' => 'number',
      ),
    );

    foreach ($tree as $brand => $values) {
      $promoters  = isset($values['Promoter']) ? count($values['Promoter']) : 0;
      $passives   = isset($values['Passive']) ? count($values['Passive']) : 0;
      $detractors = isset($values['Detractor']) ? count($values['Detractor']) : 0;

      $total = $promoters + $passives + $detractors;

      $Promoters  = $this->roundingUpValue(($promoters / $total)) * 100;
      $Passives   = $this->roundingUpValue(($passives / $total)) * 100;
      $Detractors = $this->roundingUpValue(($detractors / $total)) * 100;
      $Score      = ($Promoters - $Detractors);

      $dataTable['rows'][] = array(
        'c' => array(
          array('v' => $brand),
          array('v' => $Promoters, 'f' => $this->roundingUpValue($Promoters, 0, TRUE) . '%'),
          array('v' => $Passives, 'f' => $this->roundingUpValue($Passives, 0, TRUE) . '%'),
          array('v' => $Detractors, 'f' => $this->roundingUpValue($Detractors, 0, TRUE) . '%'),
          array('v' => $Score, 'f' => $this->roundingUpValue($Score, 0, TRUE)),
        ),
        'p' => array('Brand' => $brand)
      );
    }

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
      $category = ucwords($category);
      // set values in rows
      $lstoken = $response->getLsToken();
      $rows["{$brand}/{$category}/{$lstoken}"] = $lstoken;
    }
  }
}
