<?php

namespace PSL\ClipperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FirstQProcessAction
 */
class FirstQProcessAction
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $groupUuid;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $state;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $updated;


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
     * @return FirstQProcessAction
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
     * Set username
     *
     * @param string $username
     * @return FirstQProcessAction
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return FirstQProcessAction
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
     * @return FirstQProcessAction
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
     * @return FirstQProcessAction
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
