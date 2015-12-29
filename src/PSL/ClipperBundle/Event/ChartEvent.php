<?php

namespace PSL\ClipperBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Doctrine\Common\Collections\ArrayCollection;

class ChartEvent extends Event
{
  //int refers to project ID
  protected $order_id;
  //string refers to survey-unique-type keyed on chart map; eg nps_plus
  protected $survey_type;
  //string refers to chart-unique-name keyed on chart map; eg net_promoter
  protected $machine_name;
  //array list of brands name which offer on survey: index-oriented
  protected $brands;
  //~none for now
  protected $params;
  //list of attributes useful for chart-specific
  protected $attributes;
  //ArrayCollection of responses object
  protected $data;
  //array output for graph
  protected $data_table = array();
  // srting long title
  protected $titleLong;
  // ArrayCollection pdfFiles
  protected $pdfFiles;
  // ArrayCollection of twigs and placeholders
  protected $pdfMaps;
  
  //available "drilldown" option by survey_type
  protected static $drilldown_keys = array('countries', 'specialties', 'regions');
  protected $drilldown      = array(
    'countries'   => array(),
    'specialties' => array(),
    'regions'     => array(),
  );
  //active drilldown "filters" for current result
  protected $filters        = array();
  //all responses count
  protected $count_total    = 0;
  //filtered responses count
  protected $count_filtered = 0;
  
  /**
   * Cache
   */
  protected $use_cache;

  public function setOrderId($order_id)
  {
    $this->order_id = $order_id;
  }

  public function getOrderId()
  {
    return $this->order_id;
  }
  
  public function setChartMachineName($machine_name)
  {
    $this->machine_name = $machine_name;
  }

  public function getChartMachineName()
  {
    return $this->machine_name;
  }
  
  public function setSurveyType($survey_type)
  {
    $this->survey_type = $survey_type;
  }

  public function getSurveyType()
  {
    return $this->survey_type;
  }
  
  public function setCacheUsage($yes)
  {
    $this->use_cache = (!empty($yes));
  }
  
  public function getCacheUsage()
  {
    return (!empty($this->use_cache));
  }
  
  public function getBrands()
  {
    return $this->brands;
  }

  public function setBrands($brands)
  {
    $this->brands = $brands;
  }

  public function setParams($params)
  {
    $this->params = $params;
  }

  public function getParams()
  {
    return $this->params;
  }
  
  public function setAttributes($attributes)
  {
    $this->attributes = $attributes;
  }

  public function getAttributes()
  {
    return $this->attributes;
  }

  public function setDataTable($data_table)
  {
    $this->data_table = $data_table;
  }

  public function getDataTable()
  {
    return $this->data_table;
  }

  public function setData(ArrayCollection $data)
  {
    $this->data = $data;
  }

  /**
   * @return ArrayCollection
   */
  public function getData()
  {
    return $this->data;
  }
  
  public function setDrillDown($type, $value = FALSE) 
  {
    if (is_array($type) && ($value === FALSE)) {
      foreach ($type as $set => $values) {
        $this->setDrillDown($set, $values);
      }
      return;
    }
    
    $type   = strtolower($type);
    $values = (array) $value;
    $values = array_map('trim', $values);
    
    if ((empty($values)) || (!in_array($type, self::$drilldown_keys))) {
      //@TODO: error log
      return FALSE;
    }
    $this->drilldown[$type] = array_merge($this->drilldown[$type], $values);
  }
  
  public function getDrillDown($type = FALSE) 
  {
    if (empty($type)) {
      return $this->drilldown; 
    }
    $type  = strtolower($type);
    if ((empty($type)) || (!in_array($type, self::$drilldown_keys))) {
      //@TODO: error log
      return array();
    }
    return $this->drilldown[$type];
  }
  
  public function setFilters($filters) 
  {    
    $this->filters = $filters;
  }
  
  public function getFilters() 
  {    
    return $this->filters;
  }
  
  public function setCountTotal($count = 0) 
  {    
    $this->count_total = $count;
    if (!empty($count) && (empty($this->count_filtered))) {
      $this->count_filtered = $count;
    }
  }
  
  public function getCountTotal()
  {    
    return (int) $this->count_total;
  }
  
  public function setCountFiltered($count = 0)
  {    
    $this->count_filtered = $count;
  }
  
  public function getCountFiltered()
  {    
    return (int) $this->count_filtered;
  }

  public function setTitleLong($titleLong)
  {
    $this->titleLong = $titleLong;
  }

  public function getTitleLong()
  {
    return $this->titleLong;
  }

  public function setPdfFiles(ArrayCollection $pdfFiles)
  {
    $this->pdfFiles = $pdfFiles;
  }

  public function getPdfFiles()
  {
    return $this->pdfFiles;
  }

  public function getPdfMaps()
  {
    return $this->pdfMaps;
  }

  public function setPdfMaps($pdfMaps)
  {
    $this->pdfMaps = $pdfMaps;
  }
}
