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

use PSL\ClipperBundle\Charts\SurveyChartMap;
use PSL\ClipperBundle\Utils\MDMMapping;

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
    $this->logger->debug("eventName: {$eventName}");
    $fqg = $event->getFirstQProjectGroup();
    $fqp = $event->getFirstQProject();
    $iSurveyID = current($fqp->getLimesurveyDataByField('sid'));
    $this->logger->debug("iSurveyID: [{$iSurveyID}]");
    
    /**
     * fetch from limesurvey
     **/
    $responses = $this->fetchResponses($iSurveyID);

    /**
     * save responsens in db
     **/
    $this->saveResponses($responses['responses'], $event);

  }

  public function fetchResponses($iSurveyID)
  {
    // setup static
    static $cache_responses;
    if (!isset($cache_responses)) {
      $cache_responses = array();
    }
    if (!empty($cache_responses[$iSurveyID])) {
      return $cache_responses[$iSurveyID];
    }
    $responses = array();

    // get LS
    $ls = $this->container->get('limesurvey');
    $responses = $ls->export_responses(array(
      'iSurveyID'         => $iSurveyID,
      'sHeadingType'      => 'code',
      'sCompletionStatus' => 'complete',
      'sDocumentType'     => 'json',
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
      
    // apply static
    $cache_responses[$iSurveyID] = $responses;
    
    // return
    return $responses;
  }

  public function saveResponses($responses, FirstQProjectEvent $event)
  {
    $fqg = $event->getFirstQProjectGroup();
    $fqp = $event->getFirstQProject();
      
    // check filters
    $project_id = $fqp->getId();    
    static $filters;
    if (!isset($filters)) {
      $filters = array();
    }
    
    // get filters
    if (isset($filters[$project_id])) {
      list($group_survey_type, $project_market, $project_specialty, $survey_map) = $filters[$project_id];
    }
    else {
      // get response filters    
      $group_survey_type = $fqg->getFormDataByField('survey_type');
      $group_survey_type = current($group_survey_type);
      $project_market    = $fqp->getSheetDataByField('market');
      $project_market    = current($project_market);
      $project_specialty = $fqp->getSheetDataByField('specialty');
      $project_specialty = current($project_specialty);
      
      // get survey map
      $survey_map = SurveyChartMap::core_map($group_survey_type);
      
      // store static
      $filters[$project_id] = array(
        $group_survey_type,
        $project_market,
        $project_specialty,
        $survey_map,
      );
    }
                    
    // loop through the responses of the survey
    foreach ($responses as $response) {
      $resp = current($response);
      
      // filter by market
      $res_market = MDMMapping::reverse_lookup('countries', $resp[$survey_map['country']]);
      if (strtolower($res_market) != strtolower($project_market)) {
        continue; //skip
      }
      // filter by specialty
      $res_specialty = MDMMapping::reverse_lookup('specialties', $resp[$survey_map['specialty']]);
      if (strtolower($res_specialty) != strtolower($project_specialty)) {
        continue; //skip
      }
      
      // try to find response by token
      // NOTICE: this may cause duplication, if responses did not have token (test data); it generate new ID on the fly
      $lstoken = empty($resp['token']) ? uniqid() : $resp['token'];
      $LimeSurveyResponse = $this->em->getRepository('PSLClipperBundle:LimeSurveyResponse')->find($lstoken);

      // if no record found then create new one
      if (!$LimeSurveyResponse) {
        $lsresp = new LimeSurveyResponse();
        
        // $lsresp needs to have "id" before you can call ->persist on it
        $lsresp->setLsToken($lstoken);

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
        $this->logger->info("OK processing response, token: [{$lstoken}]");
      }
    }
    
    // commit
    $this->em->flush();
  }
}
