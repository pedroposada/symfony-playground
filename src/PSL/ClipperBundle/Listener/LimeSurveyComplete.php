<?php

namespace PSL\ClipperBundle\Listener;

use \Exception as Exception;
use \stdClass as stdClass;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use PSL\ClipperBundle\Listener\FqProcess;
use PSL\ClipperBundle\Event\FirstQProjectEvent;

class LimeSurveyComplete extends FqProcess
{

  protected function main(FirstQProjectEvent $event)
  {
    // get FirstQProject object
    $fqp = $event->getFirstQProject();

    // @TODO: Support multi market/specialty combo
    $iSurveyID = reset($fqp->getLimesurveyDataByField('sid'));

    // get LS
    $ls = $this->container->get('limesurvey');

    // get lime survey results
    $responses = $ls->export_responses(array(
      'iSurveyID' => $iSurveyID,
      'sHeadingType' => 'full',
    ));
    if( is_array($responses) ) {
      $responses = implode(', ', $responses);
      throw new Exception("LS export_responses error: [{$responses}] for fq->id: [{$fqp->getId()}] - limesurvey_complete");
    }

  }

}
