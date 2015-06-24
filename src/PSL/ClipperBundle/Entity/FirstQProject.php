<?php

namespace PSL\ClipperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class FirstQProject
{
    protected $id;

    protected $bc_order_id;

    protected $bc_product_id;

    protected $form_data_raw;

    protected $sheet_data_raw;
    
    protected $limesurvey_data_raw;

    protected $state;
    
    protected $created;
    
    protected $updated;

    /**
     * Get specific value from FormDataRaw array
     * 
     * @return mixed string|int|array
     */
    public function getFormDataByField($field_name) 
    {
      $response = array();
      
      $raw = $this->getFormDataRaw();
      $unserialized = unserialize($raw);
      if (isset($unserialized->{$field_name})) {
        $response = (array)$unserialized->{$field_name};
      }
      
      return $response;
    }

    /**
     * Get the Sheet Data unserialized
     * 
     * @return mixed string|int|array
     */
    public function getSheetDataUnserialized() 
    {
      $unserialized = unserialize($this->getSheetDataRaw());
      if (isset($unserialized)) {
        $response = $unserialized;
      }
      
      return $response;
    }

    /**
     * Get specific value from SheetDataRaw array
     * 
     * @return mixed string|int|array
     */
    public function getSheetDataByField($field_name) 
    {
      $response = array();
      
      $raw = $this->getSheetDataRaw();
      $unserialized = unserialize($raw);
      if (isset($unserialized->{$field_name})) {
        $response = (array)$unserialized->{$field_name};
      }
      
      return $response;
    }

    /**
     * Get specific value from SheetDataRaw array
     * 
     * @return mixed string|int|array
     */
    public function getLimesurveyDataByField($field_name) 
    {
      $response = array();
      
      $raw = $this->getLimesurveyDataRaw($field_name);
      $unserialized = unserialize($raw);
      if (isset($unserialized->{$field_name})) {
        $response = (array)$unserialized->{$field_name};
      }
      
      return $response;
    }

    /**
     * Get the Limesurvey Data unserialized
     * 
     * @return mixed string|int|array
     */
    public function getLimesurveyDataUnserialized() 
    {
      $unserialized = unserialize($this->getLimesurveyDataRaw());
      if (isset($unserialized)) {
        $response = $unserialized;
      }
      
      return $response;
    }

    /**
     * Get id
     *
     * @return guid 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set bc_order_id
     *
     * @param string $bcOrderId
     * @return FirstQProject
     */
    public function setBcOrderId($bcOrderId)
    {
        $this->bc_order_id = $bcOrderId;

        return $this;
    }

    /**
     * Get bc_order_id
     *
     * @return string 
     */
    public function getBcOrdertId()
    {
        return $this->bc_order_id;
    }

    /**
     * Set bc_product_id
     *
     * @param string $bcProductId
     * @return FirstQProject
     */
    public function setBcProductId($bcProductId)
    {
        $this->bc_product_id = $bcProductId;

        return $this;
    }

    /**
     * Get bc_product_id
     *
     * @return string 
     */
    public function getBcProductId()
    {
        return $this->bc_product_id;
    }

    /**
     * Set form_data_raw
     *
     * @param string $formDataRaw
     * @return FirstQProject
     */
    public function setFormDataRaw($formDataRaw)
    {
        $this->form_data_raw = $formDataRaw;

        return $this;
    }

    /**
     * Get form_data_raw
     *
     * @return string 
     */
    public function getFormDataRaw()
    {
        return $this->form_data_raw;
    }
    

    /**
     * Set sheet_data_raw
     *
     * @param string $sheetDataRaw
     * @return FirstQProject
     */
    public function setSheetDataRaw($sheetDataRaw)
    {
        $this->sheet_data_raw = $sheetDataRaw;

        return $this;
    }

    /**
     * Get sheet_data_raw
     *
     * @return string 
     */
    public function getSheetDataRaw()
    {
        return $this->sheet_data_raw;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return FirstQProject
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return FirstQProject
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return FirstQProject
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }
    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedValue()
    {
        // Add your code here
    }

    /**
     * Get bc_order_id
     *
     * @return string 
     */
    public function getBcOrderId()
    {
        return $this->bc_order_id;
    }

    /**
     * Set limesurvey_data_raw
     *
     * @param string $limesurveyDataRaw
     * @return FirstQProject
     */
    public function setLimesurveyDataRaw($limesurveyDataRaw)
    {
        $this->limesurvey_data_raw = $limesurveyDataRaw;

        return $this;
    }

    /**
     * Get limesurvey_data_raw
     *
     * @return string 
     */
    public function getLimesurveyDataRaw()
    {
        return $this->limesurvey_data_raw;
    }
}
