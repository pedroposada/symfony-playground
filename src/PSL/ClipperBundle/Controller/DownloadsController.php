<?php
/**
 * Main Clipper Downloads Controller
 */

namespace PSL\ClipperBundle\Controller;

use \Exception;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rest Controller for Clipper
 */
class DownloadsController extends Controller
{
  /**
   * Download CSV
   * /clipper/downloads
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @QueryParam(name="order_id", default=false, strict=true, nullable=false, allowBlank=false, description="FirstQGroup UUID")
   * @QueryParam(name="type", default="xls", nullable=true, description="Export file type.")
   *
   * @return Symfony\Component\HttpFoundation\Response;
   */
  public function downloadsAction(ParamFetcher $paramFetcher)
  {
    $content = null;
    $code    = 200;
    $header  = array('Content-Type' => 'text/plain');

    $order_id = $paramFetcher->get('order_id');
    $type     = $paramFetcher->get('type');

    if (empty($order_id)) {
      throw new Exception("Missing Order ID.");
    }

    try {
      //@TODO review cache strategies
      $charts = $this->getChartsByOrderId($order_id);

      //prep data structure
      $data = array(
        'complete'            => $charts,
        'available-drilldown' => array(),
        'available-brands'    => array(),
        'available-charts'    => array(),
        'charts-table-map'    => array(),
        'filtered'            => array(),
        'combined-filtered'   => array(),
      );
      $drillbits = array(
        'countries'   => 'country',
        'specialties' => 'specialty',
        'regions'     => 'region',
      );
      foreach ($charts as $index => $chart_data) {
        if ($index == 0) {
          foreach (array('drilldown', 'brands') as $key) {
            if (empty($data['available-' . $key])) {
              $data['available-' . $key] = $chart_data[$key];
            }
          }
        }
        $data['available-charts'][$index] = $chart_data['chartmachinename'];
      }
      // each drilldown (single): @see CLIP-69
      foreach ($drillbits as $drillType => $drillName) {
        if (!empty($data['available-drilldown'][$drillType])) {
          if (!isset($data['filtered'][$drillName])) {
            $data['filtered'][$drillName] = array();
          }
          foreach ($data['available-drilldown'][$drillType] as $filter) {
            $data['filtered'][$drillName][$filter] = array();
            $filter_set = array($drillName => $filter);
            $data['filtered'][$drillName][$filter] = $this->getChartsByOrderId($order_id, $filter_set);
          }
        }
      }      
      // combination drilldown - country & specialty (dual): @see CLIP-69:v2
      $locations = array_merge($data['available-drilldown']['countries'], $data['available-drilldown']['regions']);
      $locations = array_filter($locations);
      $locations = array_unique($locations);
      foreach ((array) $locations as $location) {
        foreach ((array) $data['available-drilldown']['specialties'] as $specialty) {
          $filter_set = array(
            'country'   => $location,
            'specialty' => $specialty,
          );
          $data['combined-filtered'][] = array(
            'filters' => $filter_set,
            'data'    => $this->getChartsByOrderId($order_id, $filter_set),
          );
        }
      }
      $assembler = $this->container->get('download_assembler');
      return $assembler->getDownloadFile($order_id, $this->survey_type, $type, $data);
    }
    catch(Exception $e) {
      $content = "{$e->getMessage()} - File [{$e->getFile()}] - Line [{$e->getLine()}]";
      $code = 400;
    }

    return new Response($content, $code, $header);
  }

  private function getChartsByOrderId($order_id, $drilldown = array())
  {
    $charts_helper = $this->container->get('chart_helper');
    $charts_helper->setOrderID($order_id);
    $charts_helper->setDrillDown($drilldown);
    $charts_helper->setReturnChartExtras(array(
      'chartmachinename',
      'drilldown',
      'filter',
      'brands',
      'datatable',
    ));
    if (empty($this->survey_type)) {
      $this->survey_type = $charts_helper->getSurveyType();
    }
    $charts_data = $charts_helper->getCharts();
    return $charts_data['charts'];
  }
}