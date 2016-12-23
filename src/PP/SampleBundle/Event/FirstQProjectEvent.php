<?php

namespace PP\SampleBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use PP\SampleBundle\Entity\FirstQProject;
use PP\SampleBundle\Entity\FirstQGroup;

class FirstQProjectEvent extends Event
{
  protected $fqg;
  protected $fqp;

  public function __construct(FirstQGroup $fqg, FirstQProject $fqp)
  {
    $this->fqg = $fqg;
    $this->fqp = $fqp;
  }

  /**
   * @return \PP\SampleBundle\Entity\FirstQGroup
   */
  public function getFirstQProjectGroup()
  {
    return $this->fqg;
  }
  
  /**
   * @return \PP\SampleBundle\Entity\FirstQProject
   */
  public function getFirstQProject()
  {
    return $this->fqp;
  }
  
}
