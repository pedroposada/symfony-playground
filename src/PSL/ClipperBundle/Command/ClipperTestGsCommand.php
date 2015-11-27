<?php

namespace PSL\ClipperBundle\Command;

// contrib
use \Exception;
use \stdClass;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class ClipperTestGsCommand extends ContainerAwareCommand
{
  private $logger;
  private $container;

  protected function configure()
  {
    $this->setName('clipper:test-gs')
      ->setDescription('Test connection and diagnose status of GoogleSheets.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // globals
    $this->container = $this->getContainer();
    $params = $this->container->getParameter('clipper');
    $this->logger = $this->container->get('monolog.logger.clipper');
    
    try {
      // sample form data
      $form_data = new stdClass();
      $form_data->loi = 10; 
      $form_data->ir = 1; 
      $form_data->market = 'USA';
      $form_data->specialty = 'Oncology';
      $form_data->num_participants = 10;
      
      // load requestFeasibility
      $gss = $this->container->get('google_spreadsheet');
      $feasibility = $gss->requestFeasibility($form_data);
      
      // feedback
      $this->logger->info("OK connecting to GoogleSheets and retrieving data.", array('feasibility' => $feasibility));
    }
    catch (Exception $e) {
      $this->logger->debug("{$e->getMessage()}");
      $this->logger->debug("Code: {$e->getCode()}");
      $this->logger->debug("File: {$e->getFile()} - Line: {$e->getLine()}");
    }
  }
}
