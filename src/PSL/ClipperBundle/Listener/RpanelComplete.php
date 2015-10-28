<?php

namespace PSL\ClipperBundle\Listener;

use \Exception as Exception;
use \stdClass as stdClass;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use PSL\ClipperBundle\Listener\FqProcess;
use PSL\ClipperBundle\Event\FirstQProjectEvent;

class RpanelComplete extends FqProcess
{

  protected function main(FirstQProjectEvent $event)
  {
    // get FirstQGroup and FirstQProject objects 
    $fqg = $event->getFirstQProjectGroup();
    $fqp = $event->getFirstQProject();
    
    // get survey ID
    $iSurveyID = current($fqp->getLimesurveyDataByField('sid'));
    
    // get LS
    $ls = $this->container->get('limesurvey');
    
    // check if quota has been reached
    $response = $ls->get_summary(array(
      'iSurveyID' => $iSurveyID, 
      'sStatName' => 'completed_responses', 
    ));
    if (is_array($response) && isset($response['status'])) {
      throw new Exception("Bad response from LimeSurvey with status [{$response['status']}] for fqp->id: [{$fqp->getId()}] on [get_summary]", 2);
    }
    
    $country = current($fqp->getSheetDataByField('market'));
    $specialty = current($fqp->getSheetDataByField('specialty'));
    $quota = $this->container->get('quota_map')->lookupOne($country, $specialty);
    $this->logger->debug("Lookup for country code: [{$country}] and specialty code: [{$specialty}]. Quota: [{$quota}]", array('rpanel_complete'));
    
    // if completed is less than quota, then exit
    if ($quota > $response) {
      throw new Exception("Quota ({$quota}) has not been reached yet for fqp->id: [{$fqp->getId()}]", 2);
    }
    $this->logger->debug("Quota ({$quota}) has been reached.", array('rpanel_complete'));
    
    // quota reached, EXPIRE survey
    $response = $ls->set_survey_properties(array(
      'iSurveyID' => $iSurveyID, 
      'aSurveySettings' => array(
        'expires' => self::$timestamp,
      ), 
    ));
    
    if (is_array($response) && isset($response['status'])) {
      $this->logger->debug($response['status'], array('rpanel_complete', 'set_survey_properties'));
      throw new Exception("Bad response from LimeSurvey with status [{$response['status']}] for fqp->id: [{$fqp->getId()}] on [set_survey_properties]", 2);
    }

  }

}
