<?php

namespace PSL\ClipperBundle\Listener;

use \Exception as Exception;
use \stdClass as stdClass;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use PSL\ClipperBundle\Listener\FqProcess;
use PSL\ClipperBundle\Event\FirstQProjectEvent;
use PSL\ClipperBundle\Utils\LimeSurvey as LimeSurvey;

class RpanelComplete extends FqProcess
{

  protected function main(FirstQProjectEvent $event)
  {
    // get FirstQGroup and FirstQProject objects 
    $fqg = $event->getFirstQProjectGroup();
    $fqp = $event->getFirstQProject();
    
    // get survey ID
    $iSurveyID = current($fqp->getLimesurveyDataByField('sid'));
    
    // config connection to LS
    $params_ls = $this->container->getParameter('limesurvey');
    $ls = new LimeSurvey();
    $ls->configure($params_ls['api']);
    
    // check if quota has been reached
    $response = $ls->get_summary(array(
      'iSurveyID' => $iSurveyID, 
      'sStatName' => 'completed_responses', 
    ));
    if (is_array($response) && isset($response['status'])) {
      throw new Exception("Bad response from LimeSurvey with status [{$response['status']}] for fq->id: [{$fqp->getId()}] on [get_summary]");
    }
    
    // TODO: quota needs to be dynamic per type of survey
    // $quota = current($fq->getFormDataByField('num_participants'));
    $quota = $this->container->getParameter('clipper.quota.universal');
    // if completed is less than quota, then exit
    if ($quota > $response) {
      throw new Exception("Quota has not been reached yet for fq->id: [{$fqp->getId()}]");
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
      throw new Exception("Bad response from LimeSurvey with status [{$response['status']}] for fq->id: [{$fqp->getId()}] on [set_survey_properties]");
    }

  }

}
