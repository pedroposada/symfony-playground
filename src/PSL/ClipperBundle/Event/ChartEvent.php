<?php

namespace PSL\ClipperBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Doctrine\Common\Collections\ArrayCollection;

class ChartEvent extends Event
{
  // TODO: add setter and getter for drilldown (filter options for countries, regions, specialties)
  // TODO: add setter and getter for options (google charts options object)
  // TODO: add setter and getter for charttype (google chart type)
  
  
  protected $order_id;
  protected $brands;
  protected $params;
  protected $survey_type;
  protected $chart_type;
  protected $data;
  protected $data_table = array();

  public function setOrderId($order_id)
  {
    $this->order_id = $order_id;
  }

  public function getOrderId()
  {
    return $this->order_id;
  }

  public function setParams($params)
  {
    $this->params = $params;
  }

  public function getParams()
  {
    return $this->params;
  }

  public function setSurveyType($survey_type)
  {
    $this->survey_type = $survey_type;
  }

  public function getSurveyType()
  {
    return $this->survey_type;
  }

  public function setChartType($chart_type)
  {
    $this->chart_type = $chart_type;
  }

  public function getChartType()
  {
    return $this->chart_type;
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

  public function getBrands()
  {
    return $this->brands;
  }

  public function setBrands($brands)
  {
    $this->brands = $brands;
  }
  
  /**
   * @return ArrayCollection
   */
  public function getData()
  {
    return $this->data;
  }

}
