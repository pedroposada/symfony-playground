<?php
/**
 * Machine Name      = DetractorsPromotesTheseBrands
 * Service Name      = clipper.chart.detractors_promotes_these_brands
 * Targeted Question = G003Q001
 * Targeted Template = ./src/PSL/ClipperBundle/Resources/views/Charts/detractors_promotes_these_brands.html.twig
 */
namespace PSL\ClipperBundle\Charts\Types;

use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\Types\ChartType;

class DetractorsPromotesTheseBrands extends ChartType {
  private $map        = array();
  private $qcode      = '';

  private $brands     = array();
  private $detractors = array();

  private $respondent_count = 0;

  private static $decimalPoint   = 0;
  private static $aDetractorsMax = 6;

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

    //prep detractors structure
    $this->detractors = array_combine($this->brands, array_fill(0, count($this->brands), 0));
    $this->detractors = array_combine($this->brands, array_fill(0, count($this->brands), $this->detractors));

    //extract detractors from respondent
    foreach ($event->getData() as $response) {
      //update @var $this->detractors
      $this->extractDetractors($response);
      $this->respondent_count++;
    }

    //update @var $this->detractors
    foreach ($this->brands as $brand) {
      $this->calculatePromoters($brand);
    }

    //data formation
    $dataTable = array();

    foreach ($this->brands as $index => $brand) {
      $dataTable[$index] = array(
        'title' => "{$brand} Detractors promote these brands...",
        'cols'  => array(
          array(
            'label' => '',
            'type'  => 'string',
          ),
          array(
            'label' => "% of {$brand} detractor",
            'type'  => 'string',
          ),
        ),
        'rows' => array(),
      );
      foreach ($this->detractors[$brand] as $decBrand => $perc) {
        $dataTable[$index]['rows'][] = array(
          'c' => array(
            array('v' => $decBrand),
            array('v' => $perc . '%'),
          ),
        );
      }
    }

    return $dataTable;
  }

  /**
   * Method to extract a respondent answer.
   * @method extractRespondent
   *
   * Process will populate
   * - @var $this->detractors
   *
   * Post-format:
   *   $this->detractors
   *     BRAND-A
   *       BRAND-A => 0
   *       BRAND-B => PROMOTE-COUNT
   *     BRAND-B
   *       BRAND-A => PROMOTE-COUNT
   *       BRAND-B => 0
   *     ...
   *
   * @param  LimeSurveyResponse $response
   *
   * @return void
   */
  private function extractDetractors(LimeSurveyResponse $response) {
    //getting answers
    $answers = $response->getResponseDecoded();

    //filtering answers to which related question
    $qcode = $this->qcode; //avoid lexical
    $answers = array_filter($answers, function($key) use ($qcode) {
      return (strpos($key, $qcode) !== FALSE);
    }, ARRAY_FILTER_USE_KEY);
    $answers = array_combine($this->brands, array_values($answers));

    //values assignments
    foreach ($this->brands as $brand) {
      $answers[$brand] = (int) $answers[$brand];
      if ($answers[$brand] <= self::$aDetractorsMax) {
        foreach ($this->detractors[$brand] as $decBrand => $devCount)  {
          if (($brand != $decBrand) && ($answers[$decBrand] > self::$aDetractorsMax)) {
            $this->detractors[$brand][$decBrand]++;
          }
        }
      }
    }
  }

  /**
   * Method to calculate Detractor promotes and then sort.
   * @method calculatePromoters
   *
   * Process will populate
   * - @var $this->detractors
   *
   * Post-format:
   *   $this->detractors
   *     BRAND-A
   *       BRAND-B => PROMOTE-PERCENTAGE
   *     BRAND-B
   *       BRAND-A => PROMOTE-PERCENTAGE
   *     ...
   *
   * @param  string $brand
   *
   * @return void
   */
  private function calculatePromoters($brand) {
    //remove itself; 0
    unset($this->detractors[$brand][$brand]);

    //get total count
    $promotes_count = array_values($this->detractors[$brand]);
    $promotes_count = array_filter($promotes_count);
    $promotes_count = array_sum($promotes_count);
    $per_promote = 100 / $promotes_count;

    //calculate, then fix as string
    foreach ($this->detractors[$brand] as $decBrand => $promotes) {
      $this->detractors[$brand][$decBrand] = $this->roundingUpValue($promotes * $per_promote);
    }

    //sort
    asort($this->detractors[$brand]);
  }

  /**
   * Helper method to rounding up the given value.
   * @method roundingUpValue
   *
   * @param  integer $value
   * @param  boolean|int $decPoint
   *    Assign decimal point count, or else @var self::$decimalPoint
   *
   * @param  boolean $force_string
   *    Flag to forcing the decimal point, in string.
   *
   * @return float|string
   */
  private function roundingUpValue($value = 0, $decPoint = FALSE, $force_string = FALSE) {
    if ($decPoint === FALSE) {
      $decPoint = self::$decimalPoint;
    }
    if ($force_string) {
      return number_format($value, $decPoint, '.', ',');
    }
    return round($value, $decPoint, PHP_ROUND_HALF_UP);
  }
}
