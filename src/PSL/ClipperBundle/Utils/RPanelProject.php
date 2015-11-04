<?php

// src/PSL/ClipperBundle/Utils/RPanelProject.php

namespace PSL\ClipperBundle\Utils;

use PSL\ClipperBundle\Entity\FirstQProject;
use DateTime;
use DateInterval;

/**
 * This is a wrapper object used to transport data from RPanel action to action
 * It extends the FirstQProject because it also has all the FirstQProject properties
 */
class RPanelProject
{

  protected $fq; // entitiy object

  protected $proj_id; // id value is based upon rpanel database row creation

  protected $project_sk; // id value is based upon translateapi database row creation

  protected $quote_id; // feasibility_project_quota.quota_id // quota_id in a db and quote_id in the other... I know...

  protected $proj_name; // has content from user input field 'title', they fill in the title of the project

  protected $proj_status;

  protected $launch_date; // Y-m-d H:i:s

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

  protected $expired_date;

  protected $proj_num;

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
   * Set quote_id
   */
  public function setQuoteId($quote_id)
  {
      $this->quote_id = $quote_id;
  }

  /**
   * Get quote_id
   *
   * @return int
   */
  public function getQuoteId()
  {
      return $this->quote_id;
  }

  /**
   * Set proj_name
   * @param $name
   *  has content from user input field 'title', they fill in the title of the project
   */
  public function setProjName($name)
  {
      $this->proj_name = $name;
  }

  /**
   * Get proj_name
   *
   * @return string, user input 'title', user fills in the title of the project
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
   * Set launch_date;
   */
  public function setLaunchDate($launch_date)
  {
      $this->launch_date = $launch_date;
  }

  /**
   * Get launch_date;
   *
   * @return date formatted as a string 'Y-m-d H:i:s'
   */
  public function getLaunchDate()
  {
      return $this->launch_date;
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

  /**
   * Set client_id
   */
  public function setClientId($client_id)
  {
      $this->client_id = $client_id;
  }

  /**
   * Get client_id
   *
   * @return $client_id
   */
  public function getClientId()
  {
      return $this->client_id;
  }

  /**
   * Set expired_date
   */
  public function setExpiredDate($expired_date)
  {
      $this->expired_date = $expired_date;
  }

  /**
   * Get expired_date
   *
   * @return $expired_date
   */
  public function getExpiredDate()
  {
      return $this->expired_date;
  }

  /**
   * Set proj_num
   */
  public function setProjNum($proj_num)
  {
      $this->proj_num = $proj_num;
  }

  /**
   * Get proj_num
   *
   * @return $proj_num
   */
  public function getProjNum()
  {
      // clipper order id - fqg.id
      return $this->proj_num;
  }

  /**
   * Get interval added duration.
   *
   * @param   string  $interval_spec  An interval specification. e.g. P2Y4DT6H8M
   *
   * @return  string  UNIX timestamp.
   */
  public function getAddedDuration($interval_spec)
  {
    $now = new DateTime("now");
    $added_duration = $now->add(new DateInterval($interval_spec));
    return $added_duration->format("U");
  }
}
