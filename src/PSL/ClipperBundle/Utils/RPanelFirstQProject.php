<?php

use  PSL\ClipperBundle\Entity;

namespace PSL\ClipperBundle\Utils;

/**
 * This is a wrapper object used to transport data from RPanel action to action
 * It extends the FirstQProject because it also has all the FirstQProject properties
 */
class RPanelFirstQProject extends FirstQProject
{
  
  protected $proj_id; // id in rpanel database
  
  protected $project_sk; // id in translateapi database 
  
  protected $proj_name; // might be from the $firstQProject->form_data_raw
  
  protected $specialty_id; // mapped with MDMMapping
  
  protected $country_id; // mapped with MDMMapping
  
  protected $incidence_rate;
  
  protected $length;
  
  protected $field_duration;
  
  protected $estimate_date;
  
  protected $google_sheet; // associative array from unserialized $firstQProject->sheet_data_raw
  
  protected $project_type;
  
  protected $ltid; // link type id
  
  protected $link_url;
  
  protected $created_by;
  
  protected $created_date;
  
  // hard coded values not added for now
  
  // proj_status (1)
  // proj_type (1)
  // target_size (0), 
  // target_list (0), 
  // feasibility_file (0), 
  // respondent (0), 
  // duration (0), 
  // status_id (1)
  // brand_id (1)
  // interview_length (LoI, FormData),
  // email_template_id (0)
  // link_type ('full')
  
  
  function __construct() 
  {
    
  }
  
  /**
   * Set proj_id
   */
  public function setProjId($id)
  {
      $this->proj_id = $id;
  }

  /**
   * Get proj_id
   *
   * @return int
   */
  public function getProjId()
  {
      return $this->proj_id;
  }
  
  /**
   * Set project_sk
   */
  public function setProjectSK($sk)
  {
      $this->project_sk = $sk;
  }

  /**
   * Get project_sk
   *
   * @return int
   */
  public function getProjectSK()
  {
      return $this->project_sk;
  }

  /**
   * Set proj_name
   */
  public function setProjName($name)
  {
      $this->project_sk = $name;
  }

  /**
   * Get proj_name
   *
   * @return string
   */
  public function getProjectSK()
  {
      return $this->proj_name;
  }
  
  /**
   * Set specialty_id
   */
  public function setSpecialtyId($id)
  {
      $this->specialty_id = $id;
  }

  /**
   * Get specialty_id
   *
   * @return int
   */
  public function getSpecialtyId()
  {
      return $this->specialty_id;
  }

  /**
   * Set country_id
   */
  public function setCountryId($id)
  {
      $this->country_id = $id;
  }

  /**
   * Get country_id
   *
   * @return string
   */
  public function getCountryId()
  {
      return $this->country_id;
  }
  
  /**
   * Set incidence_rate
   */
  public function setIncidenceRate($rate)
  {
      $this->incidence_rate = $rate;
  }

  /**
   * Get incidence_rate
   *
   * @return int
   */
  public function getIncidenceRate()
  {
      return $this->incidence_rate;
  }
  
  /**
   * Set length
   */
  public function setLength($length)
  {
      $this->length = $length;
  }

  /**
   * Get length
   *
   * @return int
   */
  public function getLength()
  {
      return $this->length;
  }
  
  /**
   * Set field_duration
   */
  public function setFieldDuration($duration)
  {
      $this->field_duration = $duration;
  }

  /**
   * Get field_duration
   *
   * @return int
   */
  public function getFieldDuration()
  {
      return $this->field_duration;
  }
  
  /**
   * Set estimate_date
   */
  public function setEstimateDate($date)
  {
      $this->estimate_date = $date;
  }

  /**
   * Get estimate_date
   *
   * @return date
   */
  public function getEstimateDate()
  {
      return $this->estimate_date;
  }
  
  /**
   * Set google_sheet
   */
  public function setGoogleSheet($gs)
  {
      $this->google_sheet = $gs;
  }

  /**
   * Get google_sheet
   *
   * @return associative array
   */
  public function getGoogleSheet()
  {
      return $this->google_sheet;
  }

  /**
   * Set project_type
   */
  public function setProjectType($type)
  {
      $this->project_type = $type;
  }

  /**
   * Get project_type
   *
   * @return string
   */
  public function getProjectType()
  {
      return $this->project_type;
  }
  
  /**
   * Set ltid
   */
  public function setLTId($id)
  {
      $this->ltid = $id;
  }

  /**
   * Get ltid
   *
   * @return int
   */
  public function getLTId()
  {
      return $this->ltid;
  }
  
  /**
   * Set link_url
   */
  public function setLinkUrl($url)
  {
      $this->link_url = $url;
  }

  /**
   * Get link_url
   *
   * @return string
   */
  public function getLinkUrl()
  {
      return $this->link_url;
  }
  
  /**
   * Set created_by
   */
  public function setCreatedBy($user_id)
  {
      $this->created_by = $user_id;
  }

  /**
   * Get created_by
   *
   * @return int
   */
  public function getCreatedBy()
  {
      return $this->created_by;
  }
  
  /**
   * Set created_date
   */
  public function setCreatedDate($date)
  {
      $this->created_date = $date;
  }

  /**
   * Get created_date
   *
   * @return date
   */
  public function getCreatedDate()
  {
      return $this->created_date;
  }
  
}