<?php

namespace PSL\ClipperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * FirstQGroup
 * @ORM\Entity
 */
class FirstQGroup
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     */
    protected $userId;

    /**
     * @var string
     */
    protected $orderId;

    /**
     * @var string
     */
    protected $formDataRaw;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var \DateTime
     */
    protected $created;

    /**
     * @var \DateTime
     */
    protected $updated;

    /**
     * @ORM\OneToMany(targetEntity="FirstQProject", mappedBy="group_uuid")
     */
    protected $projects;
    
    /**
     * Project id for Rpanel DB
     * This field does not persists
     */
    protected $proj_id;
    
    /**
     * Project sk for Rpanel DB
     * This field does not persists
     */
    protected $project_sk;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

    /**
     * Translate the GUUID of the group into a string
     * for the FirstQ project
     */
    public function __toString() 
    {
        return $this->id;
    }
    
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
     * Set groupUuid
     *
     * @param string $groupUuid
     * @return FirstQGroup
     */
    public function setGroupUuid($groupUuid)
    {
        $this->groupUuid = $groupUuid;

        return $this;
    }

    /**
     * Get groupUuid
     *
     * @return string 
     */
    public function getGroupUuid()
    {
        return $this->groupUuid;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return FirstQGroup
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set orderId
     * 
     * This is to hold the order id which is
     * currently a stripeToken but can be anything
     *
     * @param string $orderId
     * @return FirstQGroup
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Get orderId
     *
     * @return string 
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Set formDataRaw
     *
     * @param string $formDataRaw
     * @return FirstQGroup
     */
    public function setFormDataRaw($formDataRaw)
    {
        $this->formDataRaw = $formDataRaw;

        return $this;
    }

    /**
     * Get formDataRaw
     *
     * @return string 
     */
    public function getFormDataRaw()
    {
        return $this->formDataRaw;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return FirstQGroup
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
     * @return FirstQGroup
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
     * @return FirstQGroup
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
     * Set proj_id
     *
     * @param string $proj_id
     * @return FirstQGroup
     */
    public function setProjId($proj_id)
    {
        $this->proj_id = $proj_id;

        return $this;
    }

    /**
     * Get proj_id
     *
     * @return string 
     */
    public function getProjId()
    {
        return $this->proj_id;
    }
    
    /**
     * Set project_sk
     *
     * @param string $project_sk
     * @return FirstQGroup
     */
    public function setProjectSk($project_sk)
    {
        $this->project_sk = $project_sk;

        return $this;
    }

    /**
     * Get project_sk
     *
     * @return string 
     */
    public function getProjectSk()
    {
        return $this->project_sk;
    }
    
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
     * Get the Form Data unserialized
     * 
     * @return mixed string|int|array
     */
    public function getFormDataUnserialized() 
    {
      $unserialized = unserialize($this->getFormDataRaw());
      if (isset($unserialized)) {
        $response = $unserialized;
      }
      
      return $response;
    }
    
    /**
     * formats a FirstQGroup as a simple object for the Front End
     *
     * @return mixed firstq formated object 
     */
    public function getFormattedFirstQGroup() {
      
        $form_data = $this->getFormDataUnserialized();
        
        $firstq_formatted = array();
        $firstq_formatted['id'] = $this->id;
        $firstq_formatted['title'] = $form_data->title; // user generated
        $firstq_formatted['name'] = $form_data->name; // folio type
        $firstq_formatted['patient_type'] = $form_data->patient_type; // user generated
        $firstq_formatted['num_participants'] = $form_data->num_participants;
        $firstq_formatted['markets'] = $form_data->markets;
        $firstq_formatted['specialties'] = $form_data->specialties;
        $firstq_formatted['brands'] = $form_data->brands;
        $firstq['statements'] = $form_data->statements;
        switch ($this->state) {
            case 'ORDER_PENDING':
                $firstq_formatted['state'] = 'pending';
                break;
            case 'EMAIL_SENT':
                $firstq_formatted['state'] = 'closed';
                break;
            default:
                $firstq_formatted['state'] = 'active';
                break;
        }
        $firstq_formatted['created'] = $this->created;
        // $firstq_formatted['price'] = number_format(4995, 2, ',', ','); // Hardcoded for now
        $firstq_formatted['price'] = 4995; // Hardcoded for now
        $firstq_formatted['report_url'] = ''; // TBD
        
        return $firstq_formatted;
    }
}
