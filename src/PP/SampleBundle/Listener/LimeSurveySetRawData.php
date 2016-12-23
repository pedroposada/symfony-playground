<?php

namespace PP\SampleBundle\Listener;

use \Exception;
use \stdClass;

use PP\SampleBundle\Listener\FqProcess;
use PP\SampleBundle\Event\FirstQProjectEvent;

class LimeSurveySetRawData extends FqProcess
{
  
  protected function main(FirstQProjectEvent $event)
  {
  	// get FirstQGroup and FirstQProject objects 
    $fqg = $event->getFirstQProjectGroup();
    $fqp = $event->getFirstQProject();

    $ls = $this->container->get('limesurvey');
  	$participants_sample = current($fqp->getSheetDataByField('participants_sample'));
    $iSurveyID = current($fqp->getLimesurveyDataByField('iSurveyID'));
    $fqpid = $fqp->getId();

  	/**
     * check how many participants were added
     **/
    $response = $ls->list_participants(array(
      'iSurveyID' => $iSurveyID,
    ));
    if (isset($response['status'])) {
      // stops and tries again in next cron job
      throw new Exception("Bad response from LimeSurvey [{$response['status']}] for fqp->id: [{$fqpid}] on [list_participants]", parent::LOGWARNING);
    }
    
    // Compare participants_sample with tokens available from Limesurvey
    $response_count = count($response);
    if ($response_count < $participants_sample) {
      // stops and tries again in next cron job
      $params = $this->container->getParameter('clipper');
      $fqp->setState($params['state_codes']['limesurvey_addparticipants']);
      throw new Exception("LimeSurvey tokens available [{$response_count}] less than the participants sample [{$participants_sample}]", parent::LOGWARNING);
    }
    
  	// save limesurvey raw data
    $ls_raw_data = new stdClass();
    $ls_raw_data->sid = $iSurveyID; 
    $tokens = array();
    foreach ( $response as $participant ) {
      $tokens[] = $participant['token'];
    }
    $ls_raw_data->tokens = $tokens;
    $fqp->setLimesurveyDataRaw($this->serializer->encode($ls_raw_data));
  }

}