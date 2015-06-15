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
    $ls->configure($params_ls['api']);
    
    // // activate tokens
    // $response = $ls->activate_tokens(array(
      // 'iSurveyID' => 723936, 
    // ));
    // // add participants
    // $num_participants = 12;
    // $participants = array();
    // foreach (range(1, $num_participants) as $value) {
      // $participants[] = array(
        // 'email' => "fq{$value}@pslgroup.com",
        // 'lastname' => "fq{$value}",
        // 'firstname' => "fq{$value}",
      // );
    // }
    // $response = $ls->add_participants(array(
      // 'iSurveyID' => 723936, 
      // 'participantData' => $participants, 
    // ));
    // $response = $ls->get_participant_properties(array(
      // 'iSurveyID' => 698791, 
      // 'iTokenID' => 5, 
      // 'aTokenProperties' => array('completed', 'token'), // The properties to get
    // ));
    // list_participants
    // $participants = $ls->list_participants(array(
      // 'iSurveyID' => 698791,
      // 'bUnused' => false,
    // ));
    // $cc = new \PSL\ClipperBundle\Command\ClipperCommand();
    // $ls_raw_data = new stdClass();
    // $ls_raw_data->participants = $participants;
    // $ls_raw_data->sid = 723936; 
    // $ls_raw_data->urls = $cc->createlimeSurveyParticipantsURLs($params_ls['url_redirect'], 723936, $participants);
    // $response = $ls->get_survey_properties(array(
      // 'iSurveyID' => 698791, 
      // 'aSurveySettings' => array (
        // 'active',
        // 'autonumber_start',
        // 'emailnotificationto',
        // 'nokeyboard',
        // 'showwelcome',
        // 'additional_languages',
        // 'autoredirect',
        // 'emailresponseto',
        // 'owner_id',
        // 'showxquestions',
        // 'admin',
        // 'bounce_email',
        // 'expires',
        // 'printanswers',
        // 'sid',
        // 'adminemail',
        // 'bounceaccountencryption',
        // 'faxto',
        // 'publicgraphs',
        // 'startdate',
        // 'alloweditaftercompletion',
        // 'bounceaccounthost',
        // 'format',
        // 'publicstatistics',
        // 'template',
        // 'allowjumps',
        // 'bounceaccountpass',
        // 'googleanalyticsapikey',
        // 'refurl',
        // 'tokenanswerspersistence',
        // 'allowprev',
        // 'bounceaccounttype',
        // 'googleanalyticsstyle',
        // 'savetimings',
        // 'tokenlength',
      // 'allowregister',
        // 'bounceaccountuser',
        // 'htmlemail',
        // 'sendconfirmation',
        // 'usecaptcha',
      // 'allowsave',
        // 'bounceprocessing',
        // 'ipaddr',
        // 'showgroupinfo',
        // 'usecookie',
        // 'anonymized',
        // 'bouncetime',
        // 'language',
        // 'shownoanswer',
        // 'usetokens',
      // 'assessments',
        // 'datecreated',
        // 'listpublic',
        // 'showprogress',
      // 'attributedescriptions',
        // 'datestamp',
        // 'navigationdelay',
        // 'showqnumcode',
      // ) // The properties to get
    // ));
    
    // Debug::dump($response,6);
    $params_clip = $this->getContainer()->getParameter('clipper');
    $message = \Swift_Message::newInstance()
        ->setSubject($params_clip['email_ls_results']['subject'])
        ->setFrom($params_clip['email_ls_results']['from'])
        ->setTo($params_clip['email_ls_results']['to'])
        ->setBody(strtr($params_clip['email_ls_results']['body'], array(
          '[SID]' => 77777,
        )))
        ;
        
    $mailer = $this->getContainer()->get('mailer');
    $mailer->send($message, $failures);
    // dump($mailer);
    
  }
}