<?php

namespace PSL\ClipperBundle\Listener;

use \Exception as Exception;
use \stdClass as stdClass;
use \DateTime;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use PSL\ClipperBundle\Listener\FqProcess;
use PSL\ClipperBundle\Event\FirstQProjectEvent;
use PSL\ClipperBundle\Security\User\FWSSOQuickLoginUser;

class LimeSurveyComplete extends FqProcess
{
  public function main(FirstQProjectEvent $event)
  {
    // Get FirstQProject object
    $fqp = $event->getFirstQProject();
    $fqg = $event->getFirstQProjectGroup();

    // CLIP-75. Project complete is either:
    // When quota is reached OR time has expired; whichever comes first.
    $markets = $fqg->getFormDataByField('markets');
    $specialties = $fqg->getFormDataByField('specialties');
    $estimated_quota = $fqg->getFormDataByField('num_participants');

    $em = $this->container->get('doctrine')->getManager();
    $responses = $em->getRepository('PSLClipperBundle:LimeSurveyResponse')->findByFirstqgroup($fqg);

    $estimated_completion_date = current($fqg->getFormDataByField('completion_date'));

    $now = new \DateTime('now');
    $completion_date = new \DateTime($estimated_completion_date);

    $quota_is_reached = ($estimated_quota <= count($responses));
    $time_has_expired = ($completion_date <= $now);

    if ($quota_is_reached || $time_has_expired) {
      
    }
  }

}
