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
    // get FirstQProject object
    $fq = $event->getFirstQProject();
    
    // @TODO: Support multi market/specialty combo
    $ls_data = $fq->getLimesurveyDataUnserialized();

    $iSurveyID = $ls_data->sid;

    // config connection to LS
    $params_ls = $this->container->getParameter('limesurvey');
    $ls = new LimeSurvey();
    $ls->configure($params_ls['api']);

    // check if quota has been reached
    // $quota = $fq->getFormDataByField('num_participants'); // get total quota
    $quota = 1;
    $response = $ls->get_summary(array(
      'iSurveyID' => $iSurveyID,
      'sStatName' => 'completed_responses',
    ));
    $this->logger->debug("completed_responses = {$response}");

    if( is_array($response) && isset($response['status']) ) {
      throw new Exception("Bad response from LimeSurvey with status [{$response['status']}] for fq->id: [{$fq->getId()}] on [get_summary]");
    }

    // if completed is less than quota, then exit
    if( $quota > $response ) {
      throw new Exception("Quota has not been reached yet for fq->id: [{$fq->getId()}]");
    }

    // quota reached, expire survey
    $this->logger->debug("Quota ({$quota}) has been reached.", array('rpanel_complete'));
    $response = $ls->set_survey_properties(array(
      'iSurveyID' => $iSurveyID,
      'aSurveySettings' => array('expires' => self::$timestamp, ),
    ));

    if( is_array($response) && isset($response['status']) ) {
      $this->logger->debug($response['status'], array(
        'rpanel_complete',
        'set_survey_properties'
      ));
      throw new Exception("Bad response from LimeSurvey with status [{$response['status']}] for fq->id: [{$fq->getId()}] on [set_survey_properties]");
    }

  }

}
