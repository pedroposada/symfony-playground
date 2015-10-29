<?php

namespace PSL\ClipperBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use PSL\ClipperBundle\Event\FirstQProjectEvent;
use PSL\ClipperBundle\Security\User\FWSSOUser;

abstract class FqProcess
{
  protected $container;
  protected $logger;
  protected $serializer;
  protected $current_state;
  protected $state;
  protected $dispatcher;
  protected $user;
  static $timestamp;
  public $result;

  public function __construct(ContainerInterface $container, $state, FWSSOUser $user = null)
  {
    // this is @service_container
    $this->container = $container;
    $this->logger = $this->container->get('monolog.logger.clipper');
    $this->serializer = $container->get('clipper_serializer');
    $params = $this->container->getParameter('clipper');

    // find next state
    $keys = array_keys($params['state_codes']);
    $next_key = array_search($state, array_keys($params['state_codes'])) + 1;
    $this->next_state = isset($keys[$next_key]) ? current(array_slice($params['state_codes'], $next_key, 1)) : $params['state_codes'][$state];
    $this->state = $params['state_codes'][$state];

    self::$timestamp = time();

    // Set user as current user.
    if (empty($user)) {
      $user = $this->container->get('security.context')->getToken()->getUser();
    }
    $this->user = $user;
  }

  public function onMain(FirstQProjectEvent $event, $eventName, EventDispatcherInterface $dispatcher)
  {
    $this->dispatcher = $dispatcher;

    $this->logger->debug("eventName: {$eventName}");
    $this->logger->debug("state: {$this->state}");
    $this->logger->debug("next_state: {$this->next_state}");

    $fq = $event->getFirstQProject();

    // check state
    if ($fq->getState() == $this->state) {

      // let listeners hook into this event (before action is completed)
      $dispatcher->dispatch(strtolower("BEFORE_{$this->state}"), $event);

      $this->result = $this->main($event);
      $fq->setState($this->next_state);

      // let listeners hook into this event (after action is completed)
      $dispatcher->dispatch(strtolower("AFTER_{$this->state}"), $event);
    }
  }

  abstract protected function main(FirstQProjectEvent $event);
}
