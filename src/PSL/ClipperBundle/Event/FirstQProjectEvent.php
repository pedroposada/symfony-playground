<?php

namespace PSL\ClipperBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use PSL\ClipperBundle\Entity\FirstQProject;
use PSL\ClipperBundle\Entity\FirstQGroup;

class FirstQProjectEvent extends Event
{
  protected $fqg;
  protected $fqp;

  public function __construct(FirstQGroup $fqg, FirstQProject $fqp)
  {
    $this->fqg = $fqg;
    $this->fqp = $fqp;
  }

  public function getFirstQProjectGroup()
  {
    return $this->fqg;
  }

  public function getFirstQProject()
  {
    return $this->fqp;
  }
  
}
