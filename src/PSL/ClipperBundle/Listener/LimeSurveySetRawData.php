<?php

namespace PSL\ClipperBundle\Listener;

use \Exception;
use \stdClass;

use PSL\ClipperBundle\Listener\FqProcess;
use PSL\ClipperBundle\Event\FirstQProjectEvent;

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