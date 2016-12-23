<?php

namespace PP\SampleBundle\Command;

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
use Symfony\Component\EventDispatcher\EventDispatcher;
use Bigcommerce\Api\Client as Bigcommerce;
use Doctrine\Common\Util\Debug as Debug;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;


use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;


// custom
use PP\SampleBundle\Utils\LimeSurvey as LimeSurvey;
use PP\SampleBundle\Entity\FirstQProject as FirstQProject;
use PP\SampleBundle\Controller\RPanelController;
use PP\SampleBundle\ClipperEvents;
use PP\SampleBundle\Event\FirstQProjectEvent;
use PP\SampleBundle\Event\ChartEvent;
use PP\SampleBundle\Charts\Types\NetPromoters;

class TestCommand extends ContainerAwareCommand
{
  private $logger;
  private $container;
  private $em;

  protected function configure()
  {
    $this->setName('clipper:test')->setDescription('Test classes and methods.');
  }
  
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->logger = $this->getContainer()->get('monolog.logger.clipper');
    $this->container = $this->getContainer();
    $this->em = $this->getContainer()->get('doctrine')->getManager();
    // provide serializer class
    $encoders = array(new XmlEncoder(), new JsonEncoder());
    $normalizers = array(new ObjectNormalizer());
    $serializer = new Serializer($normalizers, $encoders);
    
    // $rpanel = new RPanelController($this->getContainer()->getParameter('rpanel'));
    // $em = $this->getContainer()->get('doctrine')->getManager();
    // $fqs = $em->getRepository('\PP\SampleBundle\Entity\FirstQProject')
      // ->findAll();
    // $num = current(current($fqs)->getFormDataByField('num_participants'));
    // Debug::dump($num);
    
    
    // get LS settings
    // $params_ls = $this->getContainer()->getParameter('limesurvey');
    // $ls = new LimeSurvey();
    // $ls->configure($params_ls['api']);
    
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
    // $cc = new \PP\SampleBundle\Command\ClipperCommand();
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
    // $params_clip = $this->getContainer()->getParameter('clipper');
    // $message = \Swift_Message::newInstance()
        // ->setSubject($params_clip['email_ls_results']['subject'])
        // ->setFrom($params_clip['email_ls_results']['from'])
        // ->setTo($params_clip['email_ls_results']['to'])
        // ->setBody(strtr($params_clip['email_ls_results']['body'], array(
          // '[SID]' => 77777,
        // )))
        // ;
//         
    // $mailer = $this->getContainer()->get('mailer');
    // $mailer->send($message, $failures);
    // dump($mailer);
    
    
    // if we get this far then deactivate survey
      // $response = $ls->get_summary(array(
        // 'iSurveyID' => 552612, 
        // 'sStatName' => 'completed_responses', 
      // ));
      
      // $lss = file_get_contents($params_ls['lss']['dir'] . '/' . $params_ls['lss']['brand_adoption']);
      // $response = $ls->import_survey(array(
        // 'sImportData' => base64_encode($lss), // BASE 64 encoded data of a lss
        // 'sImportDataType' => 'lss', 
        // 'sNewSurveyName' => "Clipper sandbox terminal", 
      // ));
      
      // $response = $ls->get_survey_properties(array(
        // 'iSurveyID' => 643645, 
        // 'aSurveySettings' => array('expires'), 
      // ));
      
      // try{
        // $params_clip = $this->getContainer()->getParameter('clipper');
        // $em = $this->getContainer()->get('doctrine')->getManager();
        // $fq = $em->getRepository('\PP\SampleBundle\Entity\FirstQProject')->find('3be65bb6-356e-11e5-b64f-cceb323b8f1b');
        // $event = new FirstQProjectEvent($fq);
        // $dispatcher = $this->getContainer()->get('event_dispatcher'); 
// 
        // // $dispatcher
          // // ->dispatch(ClipperEvents::ORDER_COMPLETE, $event)
            // // ->nextState('LIMESURVEY_CREATED')->getDispatcher()
          // // ->dispatch(ClipperEvents::LIMESURVEY_CREATED, $event)
            // // ->nextState('RPANEL_COMPLETE')->getDispatcher()
          // // ->dispatch(ClipperEvents::RPANEL_COMPLETE, $event)
            // // ->nextState('LIMESURVEY_COMPLETE')->getDispatcher()
          // // ->dispatch(ClipperEvents::LIMESURVEY_COMPLETE, $event)
            // // ->nextState('EMAIL_SENT')
        // // ;
//         
        // // $dispatcher->dispatch(ClipperEvents::ORDER_COMPLETE, $event)->nextState('LIMESURVEY_CREATED');
        // // $dispatcher->dispatch(ClipperEvents::LIMESURVEY_CREATED, $event)->nextState('RPANEL_COMPLETE');
        // // $dispatcher->dispatch(ClipperEvents::RPANEL_COMPLETE, $event)->nextState('LIMESURVEY_COMPLETE');
        // // $dispatcher->dispatch(ClipperEvents::LIMESURVEY_COMPLETE, $event)->nextState('EMAIL_SENT');
//         
        // $dispatcher->dispatch(ClipperEvents::FQ_PROCESS, $event);
      // }
      // catch (Exception $e) {
        // $this->logger->debug("File: {$e->getFile()} - Line: {$e->getLine()}");
        // $this->logger->error($e->getMessage());
      // }
//       
      // $em->flush();
      // $em->clear();
      
      // $params = $this->getContainer()->getParameter('clipper');
      // $state = 'order_complete';
      // // $state = 'limesurvey_complete';
      // $keys = array_keys($params['state_codes']);
      // $next_key = array_search($state, array_keys($params['state_codes'])) + 1; 
      // $res = isset($keys[$next_key]) ? current(array_slice($params['state_codes'], $next_key, 1)) : $params['state_codes'][$state];
      
      // // config connection to LS
      // $params_ls = $this->container->getParameter('limesurvey');
      // $ls = $this->container->get('limesurvey');
      // $ls->configure($params_ls['api']);
//       
      // // provide serializer class
      // $encoders = array(new XmlEncoder(), new JsonEncoder());
      // $normalizers = array(new ObjectNormalizer());
      // $serializer = new Serializer($normalizers, $encoders);
//       
//   
      // // get lime survey results
      // $responses = $ls->export_responses(array(
        // // 'iSurveyID' => 355196,
        // 'iSurveyID' => 563642,
        // // 'iSurveyID' => 183371,
        // 'sDocumentType' => 'json',
        // // 'sHeadingType' => 'abbreviated',
        // 'sHeadingType' => 'code',
        // 'sCompletionStatus' => 'complete',
      // ));
//       
      // $responses = base64_decode($responses);
      // $responses = $serializer->decode($responses, 'json');
      // dump($responses);
      
      // $resps = new ArrayCollection($responses->responses);
      // $expr = Criteria::expr();
      // $criteria = Criteria::create();
      // $criteria->where($expr->eq('Token', 'ejf4k93u7fi2i82'));
      // $result = $resps->matching($criteria);
      
      // $result = array();
      // foreach ($responses['responses'] as $key => $response) {
        // $resp = current($response);
        // $result[$resp['Token']] = $serializer->encode($resp, 'json');
      // }
      
      
      // change data to json
      // $em = $this->getContainer()->get('doctrine')->getManager();
      // // $fqps = $em->getRepository('PPSampleBundle:FirstQGroup')->findAll();
      // $fqps = $em->getRepository('PPSampleBundle:FirstQProject')->findAll();
      // foreach ($fqps as $fqp) {
        // $data = unserialize($fqp->getSheetDataRaw());
        // $fqp->setSheetDataRaw($serializer->encode($data, 'json'));
      // }
      // $em->flush();
      // $em->clear();
      
      
      // $em = $this->getContainer()->get('doctrine')->getManager();
      // $lsresp = $em->getRepository('PPSampleBundle:LimeSurveyResponse')->find('be25q7undhkmmaj');
      // dump($lsresp->getResponseDecoded());
      
      
      // $arr = array(
        // 'pedro/one/pepe1' => 'pedro/one/pepe1',
        // 'pedro/one/pepe2' => 'pedro/one/pepe2',
        // 'pedro/two/pepe3' => 'pedro/two/pepe3',
        // 'pedro/two/pepe4' => 'pedro/two/pepe4',
      // );
      // // $result = \PP\SampleBundle\Utils\ExplodeTree::explodeTree($arr, '/');
      // $result = $this->getContainer()->get('explode_tree')->explodeTree($arr, "/");
      // dump($result);
      
      // $event = new ChartEvent();
      // $event->setChartType('net_promoters');
      // $event->setOrderId('897b31f6-36f1-11e5-9bb2-ba8671a3df74');
      // $event->setSurveyType('nps_plus');
      // $fqg = $this->em->getReference('PPSampleBundle:FirstQGroup', '897b31f6-36f1-11e5-9bb2-ba8671a3df74');
      // $responses = $this->em->getRepository('PPSampleBundle:LimeSurveyResponse')->findByFirstqgroup($fqg);
      // $event->setData(new ArrayCollection($responses));
      // $chart = new NetPromoters($this->container, 'net_promoters');
      // $rows = $chart->dataTable($event);
      // dump($serializer->encode($rows, 'json'));
      
      // $fqg = $this->em->getReference('PPSampleBundle:FirstQGroup', '897b31f6-36f1-11e5-9bb2-ba8671a3df74');
      // // $responses = $this->em->getRepository('PPSampleBundle:LimeSurveyResponse')->findByFirstQGroupId('897b31f6-36f1-11e5-9bb2-ba8671a3df74');
      // $responses = $this->em->getRepository('PPSampleBundle:LimeSurveyResponse')->findByFirstqgroup($fqg);
      // dump($responses);
      
      // $table = $this->container->get('chart_assembler')->getChartDataTable('897b31f6-36f1-11e5-9bb2-ba8671a3df74', 'net_promoters');
      // dump($table);
      
      
      // $fqg = $this->em->getReference('PPSampleBundle:FirstQGroup', '897b31f6-36f1-11e5-9bb2-ba8671a3df74');
      // $responses = $this->em->getRepository('PPSampleBundle:LimeSurveyResponse')->findByFirstqgroup($fqg);
      // dump($responses);
      
      // $order_id = '0c1b5cca-5331-11e5-882b-b2113e0768f5';
      // $survey_type = $this->em->getRepository('PPSampleBundle:FirstQGroup')->find($order_id)->getFormDataByField('survey_type');
      // $map = $this->container->get('survey_chart_map')->map(reset($survey_type));
      // $chart_types = isset($map['chart_types']) ? $map['chart_types'] : array();
      // dump($chart_types);

      // $order_id = '60e54524-620c-11e5-882b-b2113e0768f5';
      // $fqg = $this->em->getRepository('PPSampleBundle:FirstQGroup')->find($order_id);
      // $countries = $fqg->getFormDataByField('markets');
      // $specialties = $fqg->getFormDataByField('specialties');
      // $quotas = $this->container->get('quota')->lookupMultiple($countries, $specialties);
      // $country = \PP\SampleBundle\Utils\MDMMapping::map('countries', current($countries));
      // $specialty = \PP\SampleBundle\Utils\MDMMapping::map('specialties', current($specialties));
      // $yaml = new \Symfony\Component\Yaml\Parser();
      // $lookup = $yaml->parse(file_get_contents(__DIR__ . '/../Resources/config/quota.yml'));
      // dump(array_sum($quotas));
      
    // $ls = $this->container->get('limesurvey');
    
    // $start = microtime(true);
    // $participants = array();
    // foreach (range(1, 100) as $key) {
    //   $participants[] = array('firstname' => uniqid(), 'firstname' => 'FQPID 87b92f86-8d76-11e5-bcaa-ccec3d69577a');
    // }      
    // $this->logger->debug((microtime(true) - $start) . ' seconds in create [' . count($participants) . '] participants');
    
    // $start = microtime(true);
    // try {
    //   // $ls->doAsync()->add_participants(array(
    //   //   'iSurveyID' => 147489, 
    //   //   'participantData' => $participants, 
    //   // ));
    //   $ls->add_participants(array(
    //     'iSurveyID' => 147489, 
    //     'participantData' => $participants, 
    //   ));
    //   $this->logger->debug((microtime(true) - $start) . ' duration for call');
    // }
    // catch(Exception $e) {
    //   $this->logger->debug($e->getMessage());
    // }
    
    // $start = microtime(true);
    // $resp = $ls->add_participants(array(
    //   'iSurveyID' => 147489, 
    //   'participantData' => $participants, 
    // ));
    // $this->logger->debug((microtime(true) - $start) . ' duration for sync');

    // $astart = microtime(true);
    
    // $start = microtime(true);
    // $ls->doAsync()->add_participants(array(
    //   'iSurveyID' => 147489, 
    //   'participantData' => $participants, 
    // ));
    // $this->logger->debug((microtime(true) - $start) . ' seconds in async add_participants');
    // $start = microtime(true);
    // $ls->doAsync()->add_participants(array(
    //   'iSurveyID' => 147489, 
    //   'participantData' => $participants, 
    // ));
    // $this->logger->debug((microtime(true) - $start) . ' seconds in async add_participants');
    // $start = microtime(true);
    // $ls->doAsync()->add_participants(array(
    //   'iSurveyID' => 147489, 
    //   'participantData' => $participants, 
    // ));
    // $this->logger->debug((microtime(true) - $start) . ' seconds in async add_participants');
    // $start = microtime(true);
    // $ls->doAsync()->add_participants(array(
    //   'iSurveyID' => 147489, 
    //   'participantData' => $participants, 
    // ));
    // $this->logger->debug((microtime(true) - $start) . ' seconds in async add_participants');
    // $start = microtime(true);
    // $ls->doAsync()->add_participants(array(
    //   'iSurveyID' => 147489, 
    //   'participantData' => $participants, 
    // ));
    // $this->logger->debug((microtime(true) - $start) . ' seconds in async add_participants');
    // $start = microtime(true);
    // $ls->doAsync()->add_participants(array(
    //   'iSurveyID' => 147489, 
    //   'participantData' => $participants, 
    // ));
    // $this->logger->debug((microtime(true) - $start) . ' seconds in async add_participants');
    // $start = microtime(true);
    // $ls->doAsync()->add_participants(array(
    //   'iSurveyID' => 147489, 
    //   'participantData' => $participants, 
    // ));
    // $this->logger->debug((microtime(true) - $start) . ' seconds in async add_participants');
    // $start = microtime(true);
    // $ls->doAsync()->add_participants(array(
    //   'iSurveyID' => 147489, 
    //   'participantData' => $participants, 
    // ));
    // $this->logger->debug((microtime(true) - $start) . ' seconds in async add_participants');
    // $start = microtime(true);
    // $ls->doAsync()->add_participants(array(
    //   'iSurveyID' => 147489, 
    //   'participantData' => $participants, 
    // ));
    // $this->logger->debug((microtime(true) - $start) . ' seconds in async add_participants');
    // $start = microtime(true);
    // $ls->doAsync()->add_participants(array(
    //   'iSurveyID' => 147489, 
    //   'participantData' => $participants, 
    // ));
    // $this->logger->debug((microtime(true) - $start) . ' seconds in async add_participants');
    // $start = microtime(true);
    // $ls->doAsync()->add_participants(array(
    //   'iSurveyID' => 147489, 
    //   'participantData' => $participants, 
    // ));
    // $this->logger->debug((microtime(true) - $start) . ' seconds in async add_participants');
    // $start = microtime(true);
    // $ls->doAsync()->add_participants(array(
    //   'iSurveyID' => 147489, 
    //   'participantData' => $participants, 
    // ));
    // $this->logger->debug((microtime(true) - $start) . ' seconds in async add_participants');
    
    // $this->logger->debug((microtime(true) - $astart) . ' seconds in async all');

    // $start = microtime(true);
    // $ls->add_participants(array(
    //   'iSurveyID' => 147489, 
    //   'participantData' => $participants, 
    // ));
    // $this->logger->debug((microtime(true) - $start) . ' seconds in async add_participants');
    
    
    
    // dump($ls);
    
    // $start = microtime(true);
    // $response = $ls->list_participants(array(
    //   'iSurveyID' => 147489,
    // ));
    // $this->logger->debug((microtime(true) - $start) . ' duration list_participants');
    // $count = count($response);
    // // $count = end($response);
    // $this->logger->debug($count);
    // dump($response);
    
    // $client = new \PP\SampleBundle\Utils\ClipperHttpClient;
    // $client->postNonBlocking('http://0.0.0.0:8080', array('pepe' => 'sierra', 'pedro' => 'posada'));
    // $client->jsonRpcNonBlocking('http://0.0.0.0:8080', 'testingmethod', array('pepe' => 'sierra', 'pedro' => 'posada'));
    
    // $dir = $this->container->get('kernel')->getRootdir().'/../web/pdf';
    // $this->container->get('knp_snappy.pdf')->generate('http://www.google.fr', $dir . '/file.pdf');
    // $this->container->get('knp_snappy.pdf')->generate(
    //   'http://localhost:9000/project-open/60e54524-620c-11e5-882b-b2113e0768f5', 
    //   $dir . '/'. microtime(true) .'.pdf',
    //   array(
    //     'page-size' => 'Letter',
    //     'javascript-delay' => 30000,
    //   )
    // );
    $dir = $this->container->get('kernel')->getRootdir().'/../web/html';
    $pdf = $this->container->get('knp_snappy.pdf')->getOutput( // pass file
    // $pdf = $this->container->get('knp_snappy.pdf')->getOutputFromHtml( // pass string
      // 'http://localhost:9000/project-open/60e54524-620c-11e5-882b-b2113e0768f5', 
      array(
        $dir . '/charts1.html',
        $dir . '/charts2.html',
      ),
      array(
        'page-size' => 'Letter',
        // 'javascript-delay' => 30000,
        'lowquality' => false,
        // 'load-error-handling' => 'skip',
        'debug-javascript' => true,
        // 'disable-external-links' => true,
        // 'disable-internal-links' => true,
      )
    );
    file_put_contents($dir . '/'. microtime(true) .'.pdf', $pdf);
    
  }
}