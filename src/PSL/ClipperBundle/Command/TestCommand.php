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
    // $rpanel = new RPanelController($this->getContainer()->getParameter('rpanel'));
    // $em = $this->getContainer()->get('doctrine')->getManager();
    // $fqs = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQProject')
      // ->findAll();
    // $num = current(current($fqs)->getFormDataByField('num_participants'));
    // Debug::dump($num);
    
    
    // get LS settings
    $params_ls = $this->getContainer()->getParameter('limesurvey');
    
    $ls = new LimeSurvey();
    $ls->configure(array(
      'ls_baseurl' => $params_ls['api']['ls_baseurl'],
      'ls_password' => $params_ls['api']['ls_password'],
      'ls_user' => $params_ls['api']['ls_user']
    ));
    
    // activate tokens
    $response = $ls->activate_tokens(array(
      'iSurveyID' => 723936, 
    ));
    
    // add participants
    $num_participants = 12;
    $participants = array();
    foreach (range(1, $num_participants) as $value) {
      $participants[] = array(
        'email' => "fq{$value}@pslgroup.com",
        'lastname' => "fq{$value}",
        'firstname' => "fq{$value}",
      );
    }
    $response = $ls->add_participants(array(
      'iSurveyID' => 723936, 
      'participantData' => $participants, 
    ));
    
    Debug::dump($response);
    
  }
}