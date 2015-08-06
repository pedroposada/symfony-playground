<?php

namespace PSL\ClipperBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use PSL\ClipperBundle\Event\FirstQProjectEvent;

class LimeSurveyResponses
{
  protected $container;
  protected $logger;
  static $timestamp;
  public $result;

  public function __construct(ContainerInterface $container)
  {
    // this is @service_container
    $this->container = $container;
    $this->logger = $this->container->get('monolog.logger.clipper');
    $params = $this->container->getParameter('clipper');
    self::$timestamp = time();
  }
  
  public function getResponses()
  {
    
  }
}