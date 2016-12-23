<?php

namespace PP\SampleBundle\Listener;

use \Exception as Exception;
use \stdClass as stdClass;
use \DateTime;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use PP\SampleBundle\Listener\FqProcess;
use PP\SampleBundle\Event\FirstQProjectEvent;
use PP\SampleBundle\Security\User\FWSSOQuickLoginUser;

class LimeSurveyComplete extends FqProcess
{
  public function main(FirstQProjectEvent $event)
  {
    // Get FirstQProject object
    $fqp = $event->getFirstQProject();
    $fqg = $event->getFirstQProjectGroup();

    $this->logger->debug("LimeSurvey Complete.");
  }

}
