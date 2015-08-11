<?php

namespace PSL\ClipperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class LimeSurveyResponse
{
    protected $ls_token;

    protected $member_id = 1;
    
    protected $response_raw;
    
    protected $created;
    
    protected $updated;
    /**
     * @var \PSL\ClipperBundle\Entity\FirstQProject
     */
    private $firstqproject;


    /**
     * Set ls_token
     *
     * @param string $lsToken
     * @return LimeSurveyResponse
     */
    public function setLsToken($lsToken)
    {
        $this->ls_token = $lsToken;

        return $this;
    }

    /**
     * Get ls_token
     *
     * @return string 
     */
    public function getLsToken()
    {
        return $this->ls_token;
    }

    /**
     * Set member_id
     *
     * @param integer $memberId
     * @return LimeSurveyResponse
     */
    public function setMemberId($memberId)
    {
        $this->member_id = $memberId;

        return $this;
    }

    /**
     * Get member_id
     *
     * @return integer 
     */
    public function getMemberId()
    {
        return $this->member_id;
    }

    /**
     * Set response_raw
     *
     * @param string $responseRaw
     * @return LimeSurveyResponse
     */
    public function setResponseRaw($responseRaw)
    {
        $this->response_raw = $responseRaw;

        return $this;
    }

    /**
     * Get response_raw
     *
     * @return string 
     */
    public function getResponseRaw()
    {
        return $this->response_raw;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return LimeSurveyResponse
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
     * @return LimeSurveyResponse
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
     * Set firstqproject
     *
     * @param \PSL\ClipperBundle\Entity\FirstQProject $firstqproject
     * @return LimeSurveyResponse
     */
    public function setFirstqproject(\PSL\ClipperBundle\Entity\FirstQProject $firstqproject = null)
    {
        $this->firstqproject = $firstqproject;

        return $this;
    }

    /**
     * Get firstqproject
     *
     * @return \PSL\ClipperBundle\Entity\FirstQProject 
     */
    public function getFirstqproject()
    {
        return $this->firstqproject;
    }
    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedValue()
    {
        // Add your code here
    }
}
