<?php

namespace PSL\ClipperBundle\Listener;

use \Exception as Exception;
use \stdClass as stdClass;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Util\Debug as Debug;

use PSL\ClipperBundle\Listener\FqProcess;
use PSL\ClipperBundle\Event\FirstQProjectEvent;
use PSL\ClipperBundle\Utils\LimeSurvey as LimeSurvey;
use PSL\ClipperBundle\Utils\MDMMapping as MDMMapping;

class OrderComplete extends FqProcess
{
  
  protected function main(FirstQProjectEvent $event)
  {
    // get FirstQProject object
    $fq = $event->getFirstQProject();
    
    // get LS settings
    $params_ls = $this->container->getParameter('limesurvey');

    // config connection to LS
    $ls = new LimeSurvey();
    $ls->configure($params_ls['api']);

    // array for limesurvey data
    $ls_data_raw_array = array();

    // static data for Limesurvey
    $patient_type = current($fq->getFormDataByField('patient_type'));
    $brands = $fq->getFormDataByField('brands');
    //$this->clipperBrands($fq->getFormDataByField('brands'));
    $statements = $fq->getFormDataByField('statements');
    $url_exit = $this->container->getParameter('limesurvey.url_exit');

    // array for multiple Market / Specialty
    $sheet_data = $fq->getSheetDataUnserialized();

    foreach ( $sheet_data as $key => $value ) {
      $specialty_id = MDMMapping::map('specialties', (string)$value->specialty);
      // $country_id = MDMMapping::map('countries', (string)$value->market);
      $country_id = 10;
      // @TODO: this is a hard coded value up until we get the proper mapping

      $survey_data = new stdClass();
      $survey_data->market = $specialty_id;
      $survey_data->specialty = $country_id;
      $survey_data->patients = $patient_type;
      $survey_data->brands = $brands;
      $survey_data->statements = $statements;
      $survey_data->url_exit = $url_exit;

      $sc = $this->container->get('survey_builder');
      $lss = $sc->createSurvey('nps', $survey_data);

      // import S into LS
      $iSurveyID = $ls->import_survey(array(
        'sImportData' => base64_encode($lss), // BASE 64 encoded data of a lss
        'sImportDataType' => 'lss',
        'sNewSurveyName' => "Clipper test - " . $fq->getId(),
      ));

      $this->logger->debug(Debug::toString($ls->client), array(
        'bigcommerce_complete',
        'import_survey'
      ));
      if( !is_int($iSurveyID) ) {
        throw new Exception("Bad response from LimeSurvey [{$response['status']}] for fq->id: [{$fq->getId()}] on [import_survey]");
      }

      // activate S
      $response = $ls->activate_survey(array('iSurveyID' => $iSurveyID, ));
      $this->logger->debug(Debug::toString($ls->client), array(
        'bigcommerce_complete',
        'activate_survey'
      ));
      if( !isset($response['status']) || $response['status'] != 'OK' ) {
        throw new Exception("Bad response from LimeSurvey [{$response['status']}] for fq->id: [{$fq->getId()}] on [activate_survey]");
      }

      // activate tokens
      $response = $ls->activate_tokens(array('iSurveyID' => $iSurveyID, ));
      if( !isset($response['status']) || $response['status'] != 'OK' ) {
        throw new Exception("Bad response from LimeSurvey [{$response['status']}] for fq->id: [{$fq->getId()}] on [activate_tokens]");
      }

      // add participants
      // $participants_sample = $value->participants_sample; // number of tokens
      // (links) for participants
      $participants_sample = 2;
      if( empty($participants_sample) ) {
        throw new Exception("Empty 'participants_sample' [{$participants_sample}] for fq->id: [{$fq->getId()}] on [bigcommerce_complete]");
      }

      $participants = array();
      for ( $i = 0; $i < $participants_sample; $i++ ) {
        $participants[] = array(
          'email' => "fq{$i}@pslgroup.com",
          'lastname' => "fq{$i}",
          'firstname' => "fq{$i}",
        );
      }
      $response = $ls->add_participants(array(
        'iSurveyID' => $iSurveyID,
        'participantData' => $participants,
      ));
      if( is_array($response) && isset($response['status']) ) {
        throw new Exception("Bad response from LimeSurvey [{$response['status']}] for fq->id: [{$fq->getId()}] on [add_participants]");
      }

      // save limesurvey raw data
      $ls_raw_data = new stdClass();
      $ls_raw_data->participants = $response;
      $ls_raw_data->sid = $iSurveyID;
      $ls_raw_data->urls = $this->createlimeSurveyParticipantsURLs($this->container->getParameter('limesurvey.url_redirect'), $iSurveyID, $response);

      $ls_data_raw_array[] = $ls_raw_data;
    }

    $fq->setLimesurveyDataRaw(serialize($ls_data_raw_array));
  }

  /**
   * Helper function for LimeSurvey URLs
   *
   * @param $baseURL string, base URL for limesurvey surveys, settings
   * @param $sid int, limesurvey survey id, stored in FQ entity
   * @param $participants int, stored in FormDataRaw in FQ entity
   * @return array, list of URLs for r-panel participants
   */
  public function createlimeSurveyParticipantsURLs($baseURL, $sid, $participants)
  {
    $urls = array();

    foreach ( $participants as $participant ) {
      $urls[] = strtr($baseURL, array(
        '[SID]' => $sid,
        '[LANG]' => 'en',
        '[SLUG]' => $participant['token'],
      ));
    }

    return $urls;
  }

}
