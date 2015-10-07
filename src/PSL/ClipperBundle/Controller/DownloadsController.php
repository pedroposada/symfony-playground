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
      $charts   = $this->getChartsByOrderId($order_id);

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
        foreach (array('drilldown', 'brands') as $key) {
          if (empty($data['available-' . $key])) {
            $data['available-' . $key] = $charts[0][$key];
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
      $content = "Message: [{$e->getMessage()}]";
      $code = 204;
    }

    return new Response($content, $code);
  }

  private function getChartsByOrderId($order_id, $drilldown = array())
  {
    $charts = new ArrayCollection();
    $em = $this->container->get('doctrine')->getManager();
    $fqg = $em->getRepository('PSLClipperBundle:FirstQGroup')->find($order_id);

    if (!$fqg) {
      throw new Exception("FQG with id [{$order_id}] not found");
    }

    //sanitize drilldown
    $drilldown = array_merge(
      array(
        'country'   => array(),
        'countries' => array(),
        'region'    => array(),
        'specialty' => array(),
      ),
      $drilldown
    );

    $this->survey_type = $fqg->getFormDataByField('survey_type');
    $this->survey_type = reset($this->survey_type);
    $map = $this->container->get('survey_chart_map')->map($this->survey_type);
    $assembler = $this->container->get('chart_assembler');

    foreach ($map['machine_names'] as $index => $machine_name) {
      $chEvent = $assembler->getChartEvent($order_id, $machine_name, $this->survey_type, $drilldown);
      $chart = array(
        'chartmachinename' => $machine_name,
        'drilldown'        => $chEvent->getDrillDown(),
        'filter'           => $chEvent->getFilters(),
        'brands'           => $chEvent->getBrands(),
        'datatable'        => $chEvent->getDataTable(),
      );
      $charts->add($chart);
    }

    return $charts;
  }
}