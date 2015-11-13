<?php
/**
 * PSL/ClipperBundle/Utils/ClipperChartsService.php
 *
 * Clipper Chart Helper Class
 * This is the class is a General helper to Clipper Chart.
 * 
 * @uses  PSL/ClipperBundle/Controller/ChartsController.php
 * @uses  PSL/ClipperBundle/Controller/DownloadsController.php
 *
 * @version 1.0
 * @date 2015-10-09
 **/
namespace PSL\ClipperBundle\Service;

use \Exception;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ClipperChartsService {
  private $container;
  private $em;

  //string : Order ID / FirstQGroup ID
  private $order_id;
  //object : selected FirstQGroup entity
  private $fqg;

  //string : Survey type
  private $survey_type;

  // array : Associative array of drilldown filters
  private $drilldown;
  
  // array : Filter Drilldown to specifc charts only
  private $drilldown_on_specific_chart = FALSE;
  

  // array : List of Survey fields in return
  private $return_survey_fields = array();
  // array : List of Survey attributes in return
  private $return_charts_extra = array();
  // array : List of Survey custom attributes in return
  private $return_charts_custom = array();

  // array : Compile field
  private $fields = array();
  // array : Compile charts
  private $charts = array();

  private static $customTokenEnclosure = '%%';
  private static $chartEventQueryMap = array(
    'brands'        => 'getBrands',
    'countFiltered' => 'getCountFiltered',
    'countTotal'    => 'getCountTotal',
    'datatable'     => 'getDataTable',
    'drilldown'     => 'getDrillDown',
    'filter'        => 'getFilters',
    'titleLong'     => 'getTitleLong',
  );

  public function __construct(ContainerInterface $container) 
  {
    $this->container = $container;
    $this->em = $this->container->get('doctrine')->getManager();
  }
  
  public function setOrderID($order_id) 
  {
    $fqg = $this->em->getRepository('PSLClipperBundle:FirstQGroup')->find($order_id);
    if (empty($fqg)) {
      throw new Exception("FQG with id [{$order_id}] not found");
    }
    $this->order_id = $order_id;
    $this->fqg      = $fqg;
    
    $survey_type = $this->fqg->getFormDataByField('survey_type');
    $survey_type = reset($survey_type);
    if (empty($survey_type)) {
      throw new Exception("FQG with id [{$order_id}] have no Survey type");
    }
    $this->survey_type = $survey_type;
  }
  
  public function setDrillDown($drilldown = array(), $apply_to_specific_chart = FALSE) 
  {
    //sanitize drilldown
    $drilldown = array_merge(
      array(
        'country'   => array(),
        'countries' => array(),
        'region'    => array(),
        'specialty' => array(),
        'brand'     => array(),
      ),
      $drilldown
    );
    
    $this->drilldown = (array) $drilldown;
    if (!empty($apply_to_specific_chart)) {
      $this->drilldown_on_specific_chart = (array) $apply_to_specific_chart;      
    }
  }
  
  public function setReturnFields($return_survey_fields = array())
  {
    $this->return_survey_fields = (array) $return_survey_fields;
  }
  
  public function setReturnChartExtras($return_charts_extra = array())
  {
    $this->return_charts_extra  = (array) $return_charts_extra;
  }
  
  public function setReturnChartCustoms($return_charts_custom = array())
  {
    $this->return_charts_custom = (array) $return_charts_custom;
  }
  
  public function getSurveyType() 
  {
    if (empty($this->order_id)) {
      throw new Exception("FQG id is undefined");
    }
    return $this->survey_type;
  }

  public function getCharts()
  {
    //sanitize
    $this->prepProperties();
    
    $map = $this->container->get('survey_chart_map')->map($this->survey_type);
    $assembler = $this->container->get('chart_assembler');

    $this->charts = new ArrayCollection();
    foreach ($map['machine_names'] as $index => $machine_name) {
      $drilldown = array();
      if ($this->drilldown_on_specific_chart === FALSE) {
        $drilldown = $this->drilldown;
      }
      elseif ((!empty($this->drilldown_on_specific_chart)) && (in_array($machine_name, $this->drilldown_on_specific_chart))) {
        $drilldown = $this->drilldown;
      }
      $chEvent = $assembler->getChartEvent($this->order_id, $machine_name, $this->survey_type, $drilldown);
      $chart = array('chartmachinename' => $machine_name);
      //default
      if (!empty($this->return_charts_extra)) {
        foreach ($this->return_charts_extra as $key) {
          if (isset(self::$chartEventQueryMap[$key])) {
            $f = self::$chartEventQueryMap[$key];
            $chart[$key] = $chEvent->$f();
          }
        }
      }
      //custom
      if (!empty($this->return_charts_custom)) {
        foreach ($this->return_charts_custom as $key => $mod) {
          if (strpos($mod, self::$customTokenEnclosure . 'machine_name' . self::$customTokenEnclosure) !== FALSE) {
            $chart[$key] = str_replace(self::$customTokenEnclosure . 'machine_name' . self::$customTokenEnclosure, $machine_name, $mod);
          }
          else {
            $rex = preg_quote(self::$customTokenEnclosure, '/');
            $rex = '/' . $rex . '(.*)' . $rex . '/';
            $mods = array();
            preg_match($rex, $mod, $mods);
            $mods = end($mods);
            if (isset(self::$chartEventQueryMap[$mods])) {
              $f = self::$chartEventQueryMap[$mods];
              $data = $chEvent->$f();
              $data = str_replace(self::$customTokenEnclosure . $mods . self::$customTokenEnclosure, $data, $mod);
              $chart[$key] = $data;
            }
            else {
              $chart[$key] = $mod;
            }
          }
        }
      }
      $this->charts->add($chart);
    }

    return array(
      'fields' => $this->fields,
      'charts' => $this->charts,
    );
  }
  
  public function getQuotas()
  {
    $markets     = $this->fqg->getFormDataByField('markets');
    $specialties = $this->fqg->getFormDataByField('specialties');
    // calculate "Estimated responses at completion" or global quota
    return $this->container->get('quota_map')->lookupMultiple($markets, $specialties);
  }
  
  private function prepProperties() 
  {
    //defaulting fields
    if (empty($this->return_survey_fields)) {
      $this->return_survey_fields = array(
        'survey_type',
      );
    }

    //defaulting attributes within chart
    if (empty($this->return_charts_extra)) {
      $this->return_charts_extra = array(
        'chartmachinename',
        'drilldown',
        'filter',
        'datatable',
      );
    }
    
    if (!empty($this->return_survey_fields)) {
      foreach ($this->return_survey_fields as $field) {
        $data = $this->fqg->getFormDataByField($field);
        $data = reset($data);
        $this->fields[$field] = $data;
        if ($field == 'survey_type') {
          $this->survey_type = $data;
        }
      }
    }

    if (empty($this->survey_type)) {
      $data = $this->fqg->getFormDataByField('survey_type');
      $data = reset($data);
      $this->survey_type = $data;
    }
  }
}