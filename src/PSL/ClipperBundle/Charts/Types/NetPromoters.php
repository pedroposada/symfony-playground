<?php

namespace PSL\ClipperBundle\Charts\Types;

use Doctrine\Common\Collections\ArrayCollection;

use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\Types\ChartType;

class NetPromoters extends ChartType
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
      $promoters = isset($values['Promoter']) ? count($values['Promoter']) : 0;
      $passives = isset($values['Passive']) ? count($values['Passive']) : 0;
      $detractors = isset($values['Detractor']) ? count($values['Detractor']) : 0;
      
      $total = $promoters + $passives + $detractors;
      
      $Promoters = round($promoters / $total, 2, PHP_ROUND_HALF_UP) * 100;
      $Passives = round($passives / $total, 2, PHP_ROUND_HALF_UP) * 100;
      $Detractors = round($detractors / $total, 2, PHP_ROUND_HALF_UP) * 100;
      $Score = ($Promoters - $Detractors);
      
      $dataTable['rows'][] = array(
        'c' => array(
          array('v' => $brand),
          array('v' => $Promoters, 'f' => number_format($Promoters, 0) . '%'),
          array('v' => $Passives, 'f' => number_format($Passives, 0) . '%'),
          array('v' => $Detractors, 'f' => number_format($Detractors, 0) . '%'),
          array('v' => $Score, 'f' => number_format($Score, 0)),
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
    
    // get brands array
    $brands = $event->getBrands();
    
    // question code
    $map = $this->survey_chart_map->map($event->getSurveyType());
    $qcode = $map[$event->getChartType()];
    
    // extract answers from response array
    $answers = array_filter($answers, function($key) use ($qcode) {
      return( strpos($key, $qcode) !== FALSE );
    }, ARRAY_FILTER_USE_KEY);
    $answers = array_values($answers);
    
    // Brand
    //      Type
    //          Token
    //          Token
    //      Type
    //          Token
    foreach ($brands as $key => $brand) {
      // get answer  
      $points = (int)$answers[$key]; // empty string "" = 0
      // determine category
      $category = $this->calculateCategory($points);
      // set values in rows
      $lstoken = $response->getLsToken();
      $rows["{$brand}/{$category}/{$lstoken}"] = $lstoken;
    }
    
  }
  
  /**
   * Respondent category
   * 
   * @param $points integer
   * @return string
   */
  // 0-6 = Detractor
  // 7-8 = Passive
  // 9-10 = Promoter
   private function calculateCategory($points = 0)
   {
     $category = 'Detractor';
     
     switch (TRUE) {
       
       case (in_array($points, array(7, 8))):
         $category = 'Passive';
         break;
         
       case (in_array($points, array(9, 10))):
         $category = 'Promoter';
         break;
         
     }
     
     return $category;
   }
}
