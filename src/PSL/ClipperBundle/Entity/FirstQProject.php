<?php

namespace PSL\ClipperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class FirstQProject
{
    protected $id;

    protected $bc_client_id;

    protected $bc_product_id;

    protected $form_data_raw;

    protected $sheet_data_raw;

    protected $state;
    
    protected $created;
    
    protected $updated;


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
     * Set bc_client_id
     *
     * @param string $bcClientId
     * @return FirstQProject
     */
    public function setBcClientId($bcClientId)
    {
        $this->bc_client_id = $bcClientId;

        return $this;
    }

    /**
     * Get bc_client_id
     *
     * @return string 
     */
    public function getBcClientId()
    {
        return $this->bc_client_id;
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
}
