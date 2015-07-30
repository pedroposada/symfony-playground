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
use PSL\ClipperBundle\Utils\LimeSurvey as LimeSurvey;

class LimeSurveyComplete extends FqProcess
{

  protected function main(FirstQProjectEvent $event)
  {
    // get FirstQProject object
    $fq = $event->getFirstQProject();
    
    // @TODO: Support multi market/specialty combo
    $ls_data = $fq->getLimesurveyDataUnserialized();

    // $iSurveyID = current($fq->getLimesurveyDataByField('sid'));
    $iSurveyID = $ls_data[0]->sid;

    // config connection to LS
    $params_ls = $this->container->getParameter('limesurvey');
    $ls = new LimeSurvey();
    $ls->configure($params_ls['api']);

    // get lime survey results
    $response = $ls->export_responses(array(
      'iSurveyID' => $iSurveyID,
      'sHeadingType' => 'full',
    ));
    if( is_array($response) ) {
      $reponses = implode(', ', $response);
      throw new Exception("LS export_responses error: [{$reponses}] for fq->id: [{$fq->getId()}] - limesurvey_complete");
    }

    // if we get this far then send email
    $params_clip = $this->container->getParameter('clipper');
    $message = \Swift_Message::newInstance()
      ->setFrom($params_clip['email_ls_results']['from'])
      ->setTo($params_clip['email_ls_results']['to'])
      ->setSubject(strtr($params_clip['email_ls_results']['subject'], array(
        '[URL]' => $this->container->getParameter('limesurvey.url_destination_base_sid'),
        '[SID]' => $iSurveyID,
      )))
      ->setBody(strtr($params_clip['email_ls_results']['body'], array(
        '[URL]' => $this->container->getParameter('limesurvey.url_destination_base_sid'),
        '[SID]' => $iSurveyID,
      )));

    // attachment
    $fs = new Filesystem();
    $csv = base64_decode($response);
    try {
      $fs->dumpFile('/tmp/file.csv', $csv);
    }
    catch (IOExceptionInterface $e) {
      throw new Exception("[limesurvey_complete] - An error occurred while creating your file at " . $e->getPath());
    }
    $message->attach(\Swift_Attachment::fromPath('/tmp/file.csv'));

    // send
    $failures = array();
    // addresses of failed emails
    if( !$this->container->get('mailer')->send($message, $failures) ) {
      throw new Exception("[limesurvey_complete] - Failed sending email to: " . implode(', ', $failures));
    }
    $this->logger->debug("Email: [{$message->toString()}]");

    return $fq;
  }

}
