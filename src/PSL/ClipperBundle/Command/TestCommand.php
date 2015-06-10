<?php

namespace PSL\ClipperBundle\Command;

// contrib
use \Exception as Exception;
use \stdClass as stdClass;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\LockHandler;
use Bigcommerce\Api\Client as Bigcommerce;
use Doctrine\Common\Util\Debug as Debug;

// custom
use PSL\ClipperBundle\Utils\LimeSurvey as LimeSurvey;
use PSL\ClipperBundle\Entity\FirstQProject as FirstQProject;
use PSL\ClipperBundle\Controller\RPanelController;

class TestCommand extends ContainerAwareCommand
{
  private $logger;

  protected function configure()
  {
    $this->setName('clipper:test')->setDescription('Test classes and methods.');
  }
  
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $rpanel = new RPanelController($this->getContainer()->getParameter('rpanel'));
    Debug::dump($rpanel->findAllAgencies()); 
  }
}