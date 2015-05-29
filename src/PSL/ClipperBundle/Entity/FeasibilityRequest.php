<?php
// src/PSL/ClipperBundle/Entity/FeasibilityRequest.php

namespace PSL\ClipperBundle\Entity;

// use Symfony\Component\Validator\Mapping\ClassMetadata;
// use Symfony\Component\Validator\Constraints\NotBlank;
// use Symfony\Component\Validator\Constraints\Email;
// use Symfony\Component\Validator\Constraints\Length;

class FeasibilityRequest
{

    protected $loi;

    protected $ir;

    protected $country;

    protected $specialty;

    public function getLoi()
    {
        return $this->loi;
    }

    public function setLoi($loi)
    {
        $this->loi = $loi;
    }

    public function getIr()
    {
        return $this->ir;
    }

    public function setIr($ir)
    {
        $this->ir = $ir;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function getSpecialty()
    {
        return $this->specialty;
    }

    public function setSpecialty($specialty)
    {
        $this->specialty = $specialty;
    }
    
    public function initFeasibilityRequest($loi, $ir, $country, $specialty)
    {
        $this->loi = $loi;
        $this->ir = $ir;
        $this->country = $country;
        $this->specialty = $specialty;
    }
}
