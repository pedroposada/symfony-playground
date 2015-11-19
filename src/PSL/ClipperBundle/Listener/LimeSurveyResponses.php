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

    // serializer
    $this->serializer = $container->get('clipper_serializer');
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

    $this->saveResponses($responses['responses'], $event);

  }

  public function fetchResponses($iSurveyID)
  {
    $responses = array();

    // get LS
    $ls = $this->container->get('limesurvey');
    $responses = $ls->export_responses(array(
      'iSurveyID' => $iSurveyID,
      'sHeadingType' => 'code',
      'sCompletionStatus' => 'complete',
      'sDocumentType' => 'json',
    ));

    // stop if errors from request
    if( is_array($responses) ) {
      $responses = implode(', ', $responses);
      throw new Exception("LS export_responses error: [{$responses}] for iSurveyID: [{$iSurveyID}]");
    }
    $responses = base64_decode($responses);
    $responses = $this->serializer->decode($responses, 'json');

    // stop if no responses
    if (!is_array($responses['responses']) || empty($responses['responses'])) {
      throw new Exception("LS export_responses empty for iSurveyID: [{$iSurveyID}]");
    }

    return $responses;
  }

  public function saveResponses($responses, FirstQProjectEvent $event)
  {
    $fqg = $event->getFirstQProjectGroup();
    $fqp = $event->getFirstQProject();

    // loop through the responses of the survey
    foreach ($responses as $key => $response) {
      $resp = current($response);
      
      // validation: must have $resp['token']
      if (empty($resp['token'])) {
        $this->logger->error("Response did not containing Token string.", array(
          'key'  => $key,
          'resp' => ((array) $resp),
        ));
        continue; // foreach
      }

      // try to get by token
      $lsresp = $this->em->getRepository('PSLClipperBundle:LimeSurveyResponse')->find($resp['token']);

      // if no record found then create new one
      if (!$lsresp) {
        $lsresp = new LimeSurveyResponse();
        
        // $lsresp needs to have "id" before you can call ->persist on it
        $lsresp->setLsToken($resp['token']);

        // TODO: get mid (member id) from MDM. Default to "1" for now.
        // $lsresp->setMemberId($mid);
        $lsresp->setResponseRaw($this->serializer->encode($resp, 'json'));
        $lsresp->setFirstqgroup($fqg);
        $lsresp->setFirstqproject($fqp);

        // Invoking the persist method on an entity does NOT cause an immediate
        // SQL INSERT to be issued on the database.
        // http://doctrine-orm.readthedocs.org/en/latest/reference/working-with-objects.html#persisting-entities
        $this->em->persist($lsresp);

        // feedback
        $this->logger->info("OK processing response, token: [{$resp['token']}]");
      }
    }
  }
}
