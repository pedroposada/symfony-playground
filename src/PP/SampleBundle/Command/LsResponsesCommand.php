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

class LsResponsesCommand extends ContainerAwareCommand
{
  private $logger;

  protected function configure()
  {
    $this
      ->setName('clipper:lsresponses')
      ->setDescription('Get FirstQ Projects and refresh responses from LimeSurvey.')
      ->addOption(
        'fqgid', // --fqgid="34325534"
        null, InputOption::VALUE_REQUIRED, 'If set, the task will only process this FirstQGroup'
      )
      ->addOption(
        'fqpid', // --fqpid="335353443"
        null, InputOption::VALUE_REQUIRED, 'If set, the task will only process this FirstQProject'
      )
    ;
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
    
    // flat list of items to process
    $items = array();
    
    // with options
    if ($input->getOption('fqgid') && $input->getOption('fqpid')) {
      $fqg = $em->getRepository('PPSampleBundle:FirstQGroup')->find($input->getOption('fqgid'));
      $fqp = $em->getRepository('PPSampleBundle:FirstQProject')->find($input->getOption('fqpid'));
      $items[] = array(
        'fqg' => $fqg,
        'fqp' => $fqp,
      );
    }
    // w/o options
    else {
      // FirstQGroups
      $fqgs = $em->getRepository('PPSampleBundle:FirstQGroup')->findByState($params['state_codes']['order_complete']);
      foreach ($fqgs as $fqg) {
        // FirstQProjects
        $fqps = $em->getRepository('PPSampleBundle:FirstQProject')->findByFirstqgroupAndNotState($fqg, $params['state_codes']['limesurvey_exported']);
        foreach ($fqps as $fqp) {
          $items[] = array(
            'fqg' => $fqg,
            'fqp' => $fqp,
          );
        }
      }
    }
    
    /**
     * process items
     **/
    foreach ($items as $item) {
      $this->process($item['fqg'], $item['fqp']);
    }
    
    $em->flush();
    $em->clear();
  }
  
  private function process(FirstQGroup $fqg, FirstQProject $fqp)
  {
    try {
      $dispatcher = $this->getContainer()->get('event_dispatcher'); 
      $event = new FirstQProjectEvent($fqg, $fqp);
      // main event, triggers all subscribed listeners 
      $dispatcher->dispatch(ClipperEvents::LS_REFRESH_RESPONSES, $event);
      // feedback if all is good
      $this->logger->info("OK processing FQPID: [{$fqp->getId()}]");
    }
    catch (Exception $e) {
      $this->logger->debug("File: {$e->getFile()} - Line: {$e->getLine()}");
      $this->logger->error($e->getMessage());
    }
  }
}
