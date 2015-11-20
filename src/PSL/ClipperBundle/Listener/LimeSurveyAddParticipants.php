<?php

namespace PSL\ClipperBundle\Listener;

use \Exception;
use \stdClass;

use PSL\ClipperBundle\Listener\FqProcess;
use PSL\ClipperBundle\Event\FirstQProjectEvent;

class LimeSurveyAddParticipants extends FqProcess
{
  
  protected function main(FirstQProjectEvent $event)
  {
  	// get FirstQGroup and FirstQProject objects 
    $fqg = $event->getFirstQProjectGroup();
    $fqp = $event->getFirstQProject();

    $iSurveyID = current($fqp->getLimesurveyDataByField('iSurveyID'));
    $fqpid = $fqp->getId();
    $ls = $this->container->get('limesurvey');

    /**
     * check how many participants were added
     **/
    $response = $ls->list_participants(array(
      'iSurveyID' => $iSurveyID,
    ));
    if (isset($response['status'])) {
      switch ($response['status']) {
        case 'No Tokens found':
          $response = array();
          break;
        
        default:
          throw new Exception("Bad response from LimeSurvey [{$response['status']}] on [list_participants]", parent::LOGWARNING);
          break;
      }
    }
    $count = count($response);
    $this->logger->debug("Found [{$count}] participants in LimeSurvey Id: [{$iSurveyID}]");
    
    /**
     * get amount of participants to be added for this survey
     **/
    $participants_sample = current($fqp->getSheetDataByField('participants_sample'));
    if ($this->container->hasParameter('clipper.participants_sample')) {
      // use DEV value from config_dev.yml
      $participants_sample = $this->container->getParameter('clipper.participants_sample');
    }
    $this->logger->debug("Sample has [{$participants_sample}] participants");
    if (empty($participants_sample)) {
      throw new Exception("Empty 'participants_sample' [{$participants_sample}]", parent::LOGERROR);
    }
    
    /**
     * compare and add or complete participants
     **/
    if ($count < $participants_sample) {
      
      $delta = $participants_sample - $count;
      $max_duration = $this->container->getParameter('limesurvey.max_add_participants_duration');
      $chunk = 100;
      $start = microtime(true);
      
      // make sure we don't send more than max per request
      while ($delta > 0) {
        // create chunk
        $subset = $delta < $chunk ? $delta : $chunk;
        $participants = array();
        foreach (range(1, $subset) as $key) {
          $participants[] = array('firstname' => "FQPID {$fqpid}");
        }
        // send chunk to LS
        $ls->add_participants(array(
          'iSurveyID' => $iSurveyID, 
          'participantData' => $participants, 
        ));
        $this->logger->debug("Sent [{$subset}] participants into LimeSurvey Id: [{$iSurveyID}]");
        $delta = $delta - $subset;
        // stop if we are over $max_duration
        if ($max_duration <= (microtime(true) - $start)) {
          break;
        }
      }
      
      // Stop processing this FQP. Will resume on next cron job. 
      // FQP remains in this state until $count >= $participants_sample
      $precent = ($participants_sample - $delta) / $participants_sample * 100; 
      throw new Exception("LimeSurvey OK. Processed [{$precent}]% of participants_sample.", parent::LOGINFO);
    }
  }

}