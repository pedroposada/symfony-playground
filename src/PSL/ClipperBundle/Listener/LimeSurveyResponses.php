<?php

namespace PSL\ClipperBundle\Listener;

use \Exception as Exception;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use PSL\ClipperBundle\Event\FirstQProjectEvent;
use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Utils\LimeSurvey;

class LimeSurveyResponses
{
  protected $container;
  protected $logger;
  protected $em; // entity manager
  protected $serializer;
  static $timestamp;
  public $result;

  public function __construct(ContainerInterface $container)
  {
    // this is @service_container
    $this->container = $container;
    $this->logger = $this->container->get('monolog.logger.clipper');
    $params = $this->container->getParameter('clipper');
    self::$timestamp = time();
    $this->em = $this->container->get('doctrine')->getManager();
    
    $encoders = array(new XmlEncoder(), new JsonEncoder());
    $normalizers = array(new ObjectNormalizer());
    $this->serializer = new Serializer($normalizers, $encoders);
  }
  
  public function refreshResponses(FirstQProjectEvent $event, $eventName, EventDispatcherInterface $dispatcher)
  {
    $result = array();
    
    $this->logger->debug("eventName: {$eventName}");
    
    $fqg = $event->getFirstQProjectGroup();
    $fqp = $event->getFirstQProject();
    $iSurveyID = current($fqp->getLimesurveyDataByField('sid'));
    
    $this->logger->debug("iSurveyID: [{$iSurveyID}]");
    
    $responses = $this->fetchResponses($iSurveyID);
    
    $this->saveResponses($responses['responses'], $fqp);
    
  }

  public function fetchResponses($iSurveyID)
  {
    $responses = array();
    
    // call LS api
    $params_ls = $this->container->getParameter('limesurvey');
    $ls = new LimeSurvey();
    $ls->configure($params_ls['api']);
    $responses = $ls->export_responses(array(
      'iSurveyID' => $iSurveyID,
      'sHeadingType' => 'full',
      'sCompletionStatus' => 'complete',
      'sDocumentType' => 'json',
    ));
    
    // stop if errors from request
    if( is_array($responses) ) {
      $responses = implode(', ', $responses);
      throw new Exception("LS export_responses error: [{$responses}] for fqp->id: [{$fqp->getId()}] and iSurveyID: [{$iSurveyID}]");
    }
    $responses = base64_decode($responses);
    $responses = $this->serializer->decode($responses, 'json');
    
    // stop if no responses
    if (!is_array($responses['responses']) || empty($responses['responses'])) {
      throw new Exception("LS export_responses returns empty for fqp->id: [{$fqp->getId()}] and iSurveyID: [{$iSurveyID}]");
    }
    
    return $responses;
  }

  public function saveResponses($responses, \PSL\ClipperBundle\Entity\FirstQProject $fqp)
  {
    // loop through the responses of the survey
    foreach ($responses as $key => $response) {
      $resp = current($response);
      
      // try to get by token
      $lsresp = $this->em->getRepository('PSLClipperBundle:LimeSurveyResponse')->find($resp['Token']);
      
      // if no record found then create new one
      if (!$lsresp) {
        $lsresp = new LimeSurveyResponse();
        
        // $lsresp needs to have "id" before you can call ->persist on it
        $lsresp->setLsToken($resp['Token']);
        
        // TODO: get mid (member id) from MDM. Default to "1" for now.
        // $lsresp->setMemberId($mid);
        $lsresp->setResponseRaw($this->serializer->encode($resp, 'json'));
        $lsresp->setFirstqproject($fqp);
        
        // Invoking the persist method on an entity does NOT cause an immediate 
        // SQL INSERT to be issued on the database.
        // http://doctrine-orm.readthedocs.org/en/latest/reference/working-with-objects.html#persisting-entities
        $this->em->persist($lsresp);
        
        // feedback
        $this->logger->info("OK processing response, token: [{$resp['Token']}]");
      }
    }
  }
}