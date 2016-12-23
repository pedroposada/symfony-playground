<?php

namespace PP\SampleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * @ORM\Entity
 */
class FirstQProject
{
    protected $id;

    /**
     * Group Uuid 
     * Many To One (targetEntity="FirstQGroup", inversedBy="projects")
     * Join Column (name="firstqproject_id", referencedColumnName="id")
     */
    protected $group_uuid;

    protected $sheet_data_raw;
    
    protected $limesurvey_data_raw;

    protected $state;
    
    protected $created;
    
    protected $updated;
    
    
    public function __toString()
    {
      return $this->getId();
    }
    
    /**
     * custom helper function
     * @param $raw string json encoded
     * @return array
     */
    protected function decodeRaw($raw)
    {
      $encoders = array(new XmlEncoder(), new JsonEncoder());
      $normalizers = array(new ObjectNormalizer());
      $serializer = new Serializer($normalizers, $encoders);
      
      return $serializer->decode($raw, 'json');
    }
    
    /**
     * Get the Sheet Data unserialized
     * 
     * @return mixed string|int|array
     */
    public function getSheetDataUnserialized() 
    {
      $raw = $this->getSheetDataRaw();

      return $this->decodeRaw($raw);
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
      $unserialized = $this->decodeRaw($raw);
      if (isset($unserialized[$field_name])) {
        $response = (array)$unserialized[$field_name];
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
      $unserialized = $this->decodeRaw($raw);
      if (isset($unserialized[$field_name])) {
        $response = (array)$unserialized[$field_name];
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
      $raw = $this->getLimesurveyDataRaw();
      $unserialized = $this->decodeRaw($raw);
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

    /**
     * @var \PP\SampleBundle\Entity\FirstQGroup
     */
    private $firstqgroup;


    /**
     * Set firstqgroup
     *
     * @param \PP\SampleBundle\Entity\FirstQGroup $firstqgroup
     * @return FirstQProject
     */
    public function setFirstqgroup(\PP\SampleBundle\Entity\FirstQGroup $firstqgroup = null)
    {
        $this->firstqgroup = $firstqgroup;

        return $this;
    }

    /**
     * Get firstqgroup
     *
     * @return \PP\SampleBundle\Entity\FirstQGroup 
     */
    public function getFirstqgroup()
    {
        return $this->firstqgroup;
    }
}
