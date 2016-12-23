<?php

namespace PP\SampleBundle\Command;

// contrib
use \Exception;
use \stdClass;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\LockHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\Common\Util\Debug;
use Doctrine\Common\Collections\ArrayCollection;
use Rhumsaa\Uuid\Uuid;
use Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

// custom
use PP\SampleBundle\Utils\LimeSurvey;
use PP\SampleBundle\Entity\FirstQGroup;
use PP\SampleBundle\Entity\FirstQProject;
use PP\SampleBundle\Service\RPanelService;
use PP\SampleBundle\Utils\RPanelProject;
use PP\SampleBundle\Utils\MDMMapping;
use PP\SampleBundle\ClipperEvents;
use PP\SampleBundle\Event\FirstQProjectEvent;
use PP\SampleBundle\Listener\FqProcess;

class ClipperCommand extends ContainerAwareCommand
{
  private $logger;

  protected function configure()
  {
    $this->setName('clipper:cron')
      ->setDescription('Get FirstQ Projects and process them.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // globals
    $params = $this->getContainer()->getParameter('clipper');
    $this->logger = $this->getContainer()->get('monolog.logger.clipper');
    $em = $this->getContainer()->get('doctrine')->getManager();
    
    // create the lock
    $lock = new LockHandler('clipper:cron');
    if (!$lock->lock()) {
      $this->logger->debug('The command is already running in another process.');
      return 0;
    }
    
    // FirstQ Groups
    $fqgs = $em->getRepository('PPSampleBundle:FirstQGroup')->findByState($params['state_codes']['order_complete']);

    // $this->logger->info("Found [{$fqgs->count()}] FirstQGroup(s) for processing.", array('execute'));
    foreach ($fqgs as $fqg) {
      $this->logger->info("Process FQG id: [{$fqg->getId()}]", array('execute'));
      // load all FirstQProjects
      $fqps = new ArrayCollection($em->getRepository('PPSampleBundle:FirstQProject')->findByFirstqgroup($fqg));
      // $this->logger->info("Found [{$fqps->count()}] FirstQProjects(s) for processing.", array('execute'));
      foreach ($fqps as $fqp) {
        $this->logger->info("\t");
        $this->logger->info("*--- FQP [{$fqp->getId()}] - Start ---*");
        try {
          // get dispatcher class
          $dispatcher = $this->getContainer()->get('event_dispatcher'); 
          
          // instantiate event object
          $event = new FirstQProjectEvent($fqg, $fqp);
          
          // main event, triggers subscribed listeners 
          $dispatcher->dispatch(ClipperEvents::FQ_PROCESS, $event);
          
          // feedback if all is good
          $this->logger->info("*--- FQP [{$fqp->getId()}] - Done ---*");
        }
        catch (Exception $e) {
          switch ($e->getCode()) {
            case FqProcess::LOGINFO:
              $this->logger->info($e->getMessage());
              break;
            case FqProcess::LOGWARNING:
              $this->logger->warning($e->getMessage());
              break;
            case FqProcess::LOGERROR:
            default:
              $this->logger->error($e->getMessage());
              break;
          }
          $this->logger->debug("File: {$e->getFile()} - Line: {$e->getLine()}");
          $this->logger->info("*--- FQP [{$fqp->getId()}] - In Progress ---*");
        }
        $this->logger->info("\t");
      }
    }
    
    // persist data to db
    $em->flush();
    $em->clear();
  }
}
