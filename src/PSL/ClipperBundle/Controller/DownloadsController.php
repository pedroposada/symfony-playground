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
use Symfony\Component\BrowserKit\Response;

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
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function downloadsAction(ParamFetcher $paramFetcher)
  {
    $content = null;
    $code    = 200;

    $order_id = $paramFetcher->get('order_id');
    $type     = $paramFetcher->get('type');

    if (empty($order_id)) {
      throw new Exception("Missing Order ID.");
    }

    try {
      $order_id = $paramFetcher->get('order_id');
      $type     = $paramFetcher->get('type');

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
      $assembler = $this->container->get('download_assembler');
      return $assembler->getDownloadFile($order_id, $this->survey_type, $type, $data);
    }
    catch(Exception $e) {
      $content = "{$e->getMessage()} - File [{$e->getFile()}] - Line [{$e->getLine()}]";
      $code = 204;
    }

    return new Response($content, $code);
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
      $this->survey_type = $charts_helper->getSurveyType();;
    }
    $charts_data = $charts_helper->getCharts();
    return $charts_data['charts'];
  }
}