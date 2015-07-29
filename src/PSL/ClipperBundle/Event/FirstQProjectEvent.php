<?php

namespace PSL\ClipperBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use PSL\ClipperBundle\Entity\FirstQProject;

class FirstQProjectEvent extends Event
{
  protected $fq;

  public function __construct(FirstQProject $fq)
  {
    $this->fq = $fq;
  }

  public function getFirstQProject()
  {
    return $this->fq;
  }
  
}
