<?php

namespace PSL\ClipperBundle\Command;

// contrib
use \Exception as Exception;
use \stdClass as stdClass;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\LockHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\Common\Util\Debug as Debug;
use Doctrine\Common\Collections\ArrayCollection;
use Rhumsaa\Uuid\Uuid;
use Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

// custom
use PSL\ClipperBundle\Utils\LimeSurvey as LimeSurvey;
use PSL\ClipperBundle\Entity\FirstQGroup as FirstQGroup;
use PSL\ClipperBundle\Entity\FirstQProject as FirstQProject;
use PSL\ClipperBundle\Service\RPanelService as RPanelService;
use PSL\ClipperBundle\Utils\RPanelProject as RPanelProject;
use PSL\ClipperBundle\Utils\MDMMapping as MDMMapping;
use PSL\ClipperBundle\ClipperEvents;
use PSL\ClipperBundle\Event\FirstQProjectEvent;

class LsResponsesCommand extends ContainerAwareCommand
{
  private $logger;

  protected function configure()
  {
    $this->setName('clipper:lsresponses')
      ->setDescription('Get FirstQ Projects and refresh responses from LimeSurvey.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // globals
    $params = $this->getContainer()->getParameter('clipper');
    $this->logger = $this->getContainer()->get('monolog.logger.clipper');
    $em = $this->getContainer()->get('doctrine')->getManager();
    
    // create the lock
    $lock = new LockHandler('clipper:lsresponses');
    if (!$lock->lock()) {
      $this->logger->debug('The command is already running in another process.');
      return 0;
    }
    
    // FirstQ Groups
    $fqgs = $em->getRepository('PSLClipperBundle:FirstQGroup')->findByState($params['state_codes']['order_complete']);

    $this->logger->info("Found [{$fqgs->count()}] FirstQGroup(s) for processing.", array('execute'));
    foreach ($fqgs as $fqg) {
      // load all FirstQProjects
      $fqps = $em->getRepository('PSLClipperBundle:FirstQProject')->findByFirstqgroupAndNotState($fqg, $params['state_codes']['limesurvey_exported']);
      foreach ($fqps as $fqp) {
        
        try {
          $dispatcher = $this->getContainer()->get('event_dispatcher'); 
          $event = new FirstQProjectEvent($fqg, $fqp);
          // main event, triggers all subscribed listeners 
          $dispatcher->dispatch(ClipperEvents::LS_REFRESH_RESPONSES, $event);
          // feedback if all is good
          $this->logger->info("OK processing FirstQProject with id: [{$fqp->getId()}]");
        }
        catch (Exception $e) {
          $this->logger->debug("File: {$e->getFile()} - Line: {$e->getLine()}");
          $this->logger->error($e->getMessage());
        }
        
      }
    }
    
    $em->flush();
    $em->clear();
  }
}
