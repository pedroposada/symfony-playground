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

class ClipperCommand extends ContainerAwareCommand
{
  private $logger;
  static $timestamp;

  protected function configure()
  {
    $this->setName('clipper:cron')
      ->setDescription('Get FirstQ orders and process them.')
      ->addArgument(
        'fqid',
        InputArgument::OPTIONAL,
        'FirstQ Project ID (UUID)'
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    self::$timestamp = time();
    
    // create the lock
    $lock = new LockHandler('clipper:cron');
    if (!$lock->lock()) {
      $output->writeln('The command is already running in another process.');
      return 0;
    }
    
    // globals
    $params = $this->getContainer()->getParameter('clipper');
    $this->logger = $this->getContainer()->get('monolog.logger.clipper');
    $em = $this->getContainer()->get('doctrine')->getManager();
    
    // FirstQ Groups
    $fq_groups = new ArrayCollection();
    $fqid = $input->getArgument('fqid');
    
    if ($fqid) {
      // get single fq
      $f = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQProject')->find($fqid);
      
      if (!$f) {
        $output->writeln("Invalid fqid [{$fqid}].");
        return 0;
      }
      
      $fq_groups->add($f);
    }
    else {
      // get multiple find all except with state 'email_sent'
      $fq_groups = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQGroup')->findByState($params['state_codes']['order_complete']);
    }
    
    $this->logger->info("Found [{$fq_groups->count()}] FirstQGroup(s) for processing.", array('execute'));
    
    foreach ($fq_groups as $fqg) {
      
      // load all FirstQProjects
      $fqs = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQProject')->findByFirstQGroupUUID($fqg->getId());
      
      foreach ($fqs as $fq) {
        
        try {
          
          $dispatcher = $this->getContainer()->get('event_dispatcher'); 
          $event = new FirstQProjectEvent($fqg, $fq);
          $dispatcher->dispatch(ClipperEvents::FQ_PROCESS, $event);
          
          // feedback if all is good
          $this->logger->info("OK processing FirstQProject with id: [{$fq->getId()}]");
        }
        catch (\Exception $e) {
          $this->logger->debug("File: {$e->getFile()} - {$e->getLine()}");
          $this->logger->error($e->getMessage());
        }
        
      }
    }
    
    // persist data to db
    $em->flush();
    $em->clear();
  }
}
