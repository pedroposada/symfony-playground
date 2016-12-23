<?php

namespace PP\SampleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ClipperCache
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $data;

    /**
     * @var \DateTime
     */
    private $expiries;


    /**
     * Set name
     *
     * @param string $name
     * @return ClipperCache
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set data
     *
     * @param string $data
     * @return ClipperCache
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set expiries
     *
     * @param \DateTime $expiries
     * @return ClipperCache
     */
    public function setExpiries($expiries)
    {
        $this->expiries = $expiries;

        return $this;
    }

    /**
     * Get expiries
     *
     * @return \DateTime
     */
    public function getExpiries()
    {
        return $this->expiries;
    }
}
