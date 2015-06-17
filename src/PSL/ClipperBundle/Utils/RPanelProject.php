<?php

namespace PSL\ClipperBundle\Utils;

use  PSL\ClipperBundle\Entity\FirstQProject;


/**
 * This is a wrapper object used to transport data from RPanel action to action
 * It extends the FirstQProject because it also has all the FirstQProject properties
 */
class RPanelProject
{
  
  protected $fq; // entitiy object
  
  protected $proj_id; // id value is based upon rpanel database row creation
  
  protected $project_sk; // id value is based upon translateapi database row creation
  
  protected $proj_name;
  
  protected $proj_status;
  
  protected $proj_type;
  
  protected $specialty_id; // mapped with MDMMapping
  
  protected $country_id; // mapped with MDMMapping
  
  protected $incidence_rate;
  
  protected $length;
  
  protected $target_size;
  
  protected $target_list;
  
  protected $feasibility_file;
  
  protected $respondent;
  
  protected $duration;
  
  protected $field_duration;
  
  protected $status_id;
  
  protected $brand_id;
  
  protected $email_template_id;
  
  protected $num_participants;
  
  protected $google_sheet;
  
  protected $estimate_date;
  
  protected $project_type;
  
  protected $ltid; // id value is based upon translateapi database row creation
  
  protected $link_url;
  
  protected $link_type;
  
  protected $created_by;
  
  protected $created_date;
  
  function __construct(FirstQProject $fq) 
  {
    $this->fq = $fq;
  }
  
  function __call($callback, $param_arr)
  {
    return call_user_func_array(array($this->fq, $callback), $param_arr);
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
      $this->proj_name = $name;
  }

  /**
   * Get proj_name
   *
   * @return string
   */
  public function getProjName()
  {
      return $this->proj_name;
  }

  /**
   * Set proj_status;
   */
  public function setProjStatus($status)
  {
      $this->proj_status = $status;
  }

  /**
   * Get proj_status;
   *
   * @return int
   */
  public function getProjStatus()
  {
      return $this->proj_status;
  }

  /**
   * Set proj_type;
   */
  public function setProjType($type)
  {
      $this->proj_type = $type;
  }

  /**
   * Get proj_type;
   *
   * @return int
   */
  public function getProjType()
  {
      return $this->proj_type;
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
   * Set target_size
   */
  public function setTargetSize($size)
  {
      $this->target_size = $size;
  }

  /**
   * Get target_size
   *
   * @return int
   */
  public function getTargetSize()
  {
      return $this->target_size;
  }

  /**
   * Set target_list
   */
  public function setTargetList($list)
  {
      $this->target_list = $list;
  }

  /**
   * Get target_list
   *
   * @return int
   */
  public function getTargetList()
  {
      return $this->target_list;
  }

  /**
   * Set feasibility_file
   */
  public function setFeasibilityFile($file)
  {
      $this->feasibility_file = $file;
  }

  /**
   * Get feasibility_file
   *
   * @return int
   */
  public function getFeasibilityFile()
  {
      return $this->feasibility_file;
  }

  /**
   * Set respondent
   */
  public function setRespondent($respondent)
  {
      $this->respondent = $respondent;
  }

  /**
   * Get respondent
   *
   * @return int
   */
  public function getRespondent()
  {
      return $this->respondent;
  }
  
  /**
   * Set duration
   */
  public function setDuration($duration)
  {
      $this->duration = $duration;
  }

  /**
   * Get duration
   *
   * @return int
   */
  public function getDuration()
  {
      return $this->duration;
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
   * Set status_id
   */
  public function setStatusId($id)
  {
      $this->status_id = $id;
  }

  /**
   * Get status_id
   *
   * @return int
   */
  public function getStatusId()
  {
      return $this->status_id;
  }

  /**
   * Set brand_id
   */
  public function setBrandId($id)
  {
      $this->brand_id = $id;
  }

  /**
   * Get brand_id
   *
   * @return int
   */
  public function getBrandId()
  {
      return $this->brand_id;
  }

  /**
   * Set email_template_id
   */
  public function setEmailTemplateId($id)
  {
      $this->email_template_id = $id;
  }

  /**
   * Get email_template_id
   *
   * @return int
   */
  public function getEmailTemplateId()
  {
      return $this->email_template_id;
  }

  /**
   * Set num_participants
   */
  public function setNumParticipants($num)
  {
      $this->num_participants = $num;
  }

  /**
   * Get num_participants
   *
   * @return int
   */
  public function getNumParticipants()
  {
      return $this->num_participants;
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
   * Set link_type
   */
  public function setLinkType($type)
  {
      $this->link_type = $type;
  }

  /**
   * Get link_type
   *
   * @return string
   */
  public function getLinkType()
  {
      return $this->link_type;
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