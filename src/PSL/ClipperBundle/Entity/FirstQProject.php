<?php
// src/Blogger/BlogBundle/Entity/Blog.php

namespace PSL\ClipperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="PSL\ClipperBundle\Entity\Repository\FirstQProjectRepository")
 * @ORM\Table(name="firstq_project")
 * @ORM\HasLifecycleCallbacks()
 */
class FirstQProject
{
  
    public function __construct()
    {
      $this->setCreated(new \DateTime());
      $this->setUpdated(new \DateTime());
    }
    
    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedValue()
    {
      $this->setUpdated(new \DateTime());
    }
    
    /**
     * @ORM\Column(type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=200, options={"comment":"Bigcommerce client id"})
     */
    protected $bc_client_id;

    /**
     * @ORM\Column(type="string", length=200, options={"comment":"id of the Bigcommerce product created"})
     */
    protected $bc_product_id;

    /**
     * @ORM\Column(type="text", options={"comment":"result of the form submission, as json or as associative array"})
     */
    protected $form_data_raw;

    /**
     * @ORM\Column(type="text", options={"comment":"result of the Google Spreadsheet as json or as associative array"})
     */
    protected $sheet_data_raw;

    /**
     * @ORM\Column(type="string", length=100, options={"comment":"last error or success code. Use to keep track of the request"})
     */
    protected $state;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;


    /**
     * Get id
     *
     * @return integer 
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
}
