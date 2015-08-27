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

  private $detractors = array();
  private $respondent_count = 0;

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
    parent::$decimal_point = 0;

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
    $answers = $this->filterAnswersToQuestionMap($answers, 'int');

    //values assignments
    foreach ($this->brands as $brand) {
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


}
