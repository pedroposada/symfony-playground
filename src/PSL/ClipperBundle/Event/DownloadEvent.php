<?php
namespace PSL\ClipperBundle\Event;

use PSL\ClipperBundle\ClipperEvents;
use Symfony\Component\EventDispatcher\Event;

class DownloadEvent extends Event
{
  //int refers to project ID
  protected $order_id;
  //string refers to survey-unique-type keyed on chart map; eg nps_plus
  protected $survey_type;
  //string refers to file output format / type
  protected $download_type;
  //object Response of containing the file
  protected $file_response;

  //array pool of charts API data with other details
  protected $raw_data;
  
  // for validation: add this reference as more SurveyType added
  private static $survey_type_map = array(
    'nps_plus',
  );
  
  // for validation: add this reference as more DownloadType supported
  private static $download_type_map = array(
    'dev', // @TODO: remove this
    'xls',
  );

  public function setOrderId($order_id)
  {
    $this->order_id = $order_id;
  }

  public function getOrderId()
  {
    return $this->order_id;
  }

  public function setSurveyType($survey_type)
  {
    if (!in_array($survey_type, self::$survey_type_map)) {
      throw new \Exception("Unsupported survey type: '{$survey_type}'.");      
    }
    $this->survey_type = $survey_type;
  }

  public function getSurveyType()
  {
    return $this->survey_type;
  }

  public function setDownloadType($download_type)
  {
    if (!in_array($download_type, self::$download_type_map)) {
      throw new \Exception("Unsupported download type: '{$download_type}'.");      
    }
    $this->download_type = $download_type;    
  }

  public function getDownloadType()
  {
    return $this->download_type;
  }

  public function setRawData($raw_data)
  {
    $this->raw_data = $raw_data;
  }

  public function getRawData()
  {
    return $this->raw_data;
  }

  public function setFile($file_response)
  {
    $this->file_response = $file_response;
  }

  public function getFile()
  {
    return $this->file_response;
  }
}
