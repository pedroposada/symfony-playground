<?php

namespace PP\SampleBundle\Listener;

use \Exception as Exception;
use \stdClass as stdClass;

use PP\SampleBundle\Listener\FqProcess;
use PP\SampleBundle\Event\FirstQProjectEvent;

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
      throw new Exception("Bad response from LimeSurvey with status [{$response['status']}] for fqp->id: [{$fqp->getId()}] on [get_summary]", parent::LOGWARNING);
    }
    
    $country = current($fqp->getSheetDataByField('market'));
    $specialty = current($fqp->getSheetDataByField('specialty'));
    $quota = current($fqp->getSheetDataByField('num_participants'));
    $this->logger->debug("Lookup for country code: [{$country}] and specialty code: [{$specialty}]. Quota: [{$quota}]", array('rpanel_complete'));

    $estimated_completion_date = current($fqg->getFormDataByField('completion_date'));
    $now = new \DateTime('now');
    $completion_date = new \DateTime($estimated_completion_date);

    // CLIP-75. Project complete is either:
    // When quota is reached OR time has expired; whichever comes first.
    $quota_is_reached = ($quota <= $response);
    $time_has_expired = ($completion_date <= $now);

    $this->logger->debug("Estimated completion date: {$estimated_completion_date}");
    
    // if completed is less than quota, then exit
    if (!$quota_is_reached && !$time_has_expired) {
      throw new Exception("Quota ({$quota}) has not been reached yet. Current: {$response}.", parent::LOGINFO);
    }

    $message = ($time_has_expired) ? "Time has expired ({$estimated_completion_date})." : "Quota ({$quota}) has been reached.";
    
    $this->logger->debug($message, array('rpanel_complete'));
    
    // quota reached, EXPIRE survey
    $response = $ls->set_survey_properties(array(
      'iSurveyID' => $iSurveyID, 
      'aSurveySettings' => array(
        'expires' => parent::$timestamp,
      ), 
    ));
    if (is_array($response) && isset($response['status'])) {
      $this->logger->debug($response['status'], array('rpanel_complete', 'set_survey_properties'));
      throw new Exception("Bad response from LimeSurvey with status [{$response['status']}] on [set_survey_properties]", parent::LOGWARNING);
    }

  }

}
