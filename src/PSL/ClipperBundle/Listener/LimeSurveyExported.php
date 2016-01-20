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

class LimeSurveyExported extends FqProcess
{
  public function main(FirstQProjectEvent $event)
  {
    // Get FirstQGroup object
    $fqg = $event->getFirstQProjectGroup();

    // Resources
    $logger = $this->container->get('monolog.logger.clipper');
    $em = $this->container->get('doctrine')->getManager();
    $parameters_clipper = $this->container->getParameter('clipper');

    // If order was closed by other project itaration, just return.
    if ($fqg->getState() === $parameters_clipper['state_codes']['order_closed']) {
      $logger->info('Order already closed.');
      return;
    }

    // Get projects repo
    $fqps = $em->getRepository('PSLClipperBundle:FirstQProject');
    // Get count of projects
    $fqps_total = $fqps->createQueryBuilder('fqp')
      ->select('count(fqp.id)')
      ->where('fqp.firstqgroup = :fqgid')
      ->setParameter('fqgid', $fqg)
      ->getQuery()
      ->getSingleScalarResult();
    // Get count of exported projects
    $fqps_exported = $fqps->createQueryBuilder('fqp')
      ->select('count(fqp.id)')
      ->where('fqp.state = :state')
      ->andWhere('fqp.firstqgroup = :fqgid')
      ->setParameter('state', $parameters_clipper['state_codes']['limesurvey_exported'])
      ->setParameter('fqgid', $fqg)
      ->getQuery()
      ->getSingleScalarResult();

    $logger->info('Exported: ' . $fqps_exported . '. Total:' . $fqps_total);

    // Compare
    if ($fqps_exported === $fqps_total) {
      // Completed
      $logger->info('FQG ' . $fqg->getId() . '\'s projects are completely exported. Closing group.');
      $fqg->setState($parameters_clipper['state_codes']['order_closed']);
      $em->persist($fqg);
      $em->flush();
    } else {
      // Still not completed
      $logger->info('FQG ' . $fqg->getId() . ' still needs ' . ($fqps_total - $fqps_exported) . ' exported projects to be closed.');
    }
    
  }

}
