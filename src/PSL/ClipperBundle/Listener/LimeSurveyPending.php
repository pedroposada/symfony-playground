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
use PSL\ClipperBundle\ClipperEvents;

class LimeSurveyPending extends FqProcess
{
  
  protected function main(FirstQProjectEvent $event)
  {
    // get FirstQGroup and FirstQProject objects 
    $fqg = $event->getFirstQProjectGroup();
    $fqp = $event->getFirstQProject();
    
    // get LS
    $ls = $this->container->get('limesurvey');
    
    // array for limesurvey data
    $ls_data_raw_array = array();
    
    // Unserialize form and sheet data
    $form_data = $fqg->getFormDataUnserialized();
    $sheet_data = $fqp->getSheetDataUnserialized();
    
    // Mapping
    $specialty_id = MDMMapping::map('specialties', $sheet_data['specialty']);
    $country_id = MDMMapping::map('countries', $sheet_data['market']);
    
    // Survey data settings
    $survey_data = new stdClass();
    $survey_data->market = $country_id;
    $survey_data->specialty = $specialty_id;
    $survey_data->patients = $form_data['patient_type'];
    $survey_data->brands = $form_data['brands'];
    $survey_data->attributes = $form_data['attributes'];
    $survey_data->url_exit = $this->container->getParameter('limesurvey.url_exit');
    $type = $form_data['survey_type'];
    
    $sc = $this->container->get('survey_builder');
    $lss = $sc->createSurvey($type, $survey_data);
    
    // import S into LS
    $iSurveyID = $ls->import_survey(array(
      'sImportData' => base64_encode($lss), // BASE 64 encoded data of a lss
      'sImportDataType' => 'lss', 
      'sNewSurveyName' => $form_data['title'], 
    ));
    $this->logger->debug("form_data[title] {$form_data['title']}", array('LimeSurveyPending', 'import_survey'));
    if (!is_int($iSurveyID)) {
      throw new Exception("Bad response from LimeSurvey [{$response['status']}] for fqp->id: [{$fqp->getId()}] on [import_survey]", 2);
    }
    
    // activate S
    $response = $ls->activate_survey(array(
      'iSurveyID' => $iSurveyID, 
    ));
    $this->logger->debug("iSurveyID [{$iSurveyID}]", array('LimeSurveyPending', 'activate_survey'));
    if (!isset($response['status']) || $response['status'] != 'OK') {
      throw new Exception("Bad response from LimeSurvey [{$response['status']}] for fqp->id: [{$fqp->getId()}] on [activate_survey]", 2);
    }
    
    // activate tokens
    $response = $ls->activate_tokens(array(
      'iSurveyID' => $iSurveyID, 
    ));
    if (!isset($response['status']) || $response['status'] != 'OK') {
      throw new Exception("Bad response from LimeSurvey [{$response['status']}] for fqp->id: [{$fqp->getId()}] on [activate_tokens]", 2);
    }
    
    // get amount of participants to be added for this survey
    if (!$this->container->hasParameter('clipper.participants_sample')) {
      $participants_sample = current($fqp->getSheetDataByField('participants_sample'));
    } else {
      $participants_sample = $this->container->getParameter('clipper.participants_sample');
      // use sheet data when it's negative value , eg. -1.
      if ($participants_sample < 0) {
        $participants_sample = current($fqp->getSheetDataByField('participants_sample'));
      }  
    }

    if (empty($participants_sample)) {
      throw new Exception("Empty 'participants_sample' [{$participants_sample}] for fqp->id: [{$fqp->getId()}]", 2);
    }
    
    $participants = array();
    for ($i = 0; $i < $participants_sample; $i++) { 
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
    if (is_array($response) && isset($response['status'])) {
      throw new Exception("Bad response from LimeSurvey [{$response['status']}] for fqp->id: [{$fqp->getId()}] on [add_participants]", 2);
    }
    
    // @TODO : Proper mapping in MDMMapping / GeoMapper / CountryFWSSO
    // Language Mapping
    $languageMap = array(
      'France' => 'fr',
      'Germany' => 'de',
      'Italy' => 'it',
      'Spain' => 'es',
    );

    $languageCode = isset($languageMap[$sheet_data['market']]) ? $languageMap[$sheet_data['market']] : 'en';

    // Extract token from limesurvey response, the rest of data are not needed for now.
    $formatResponse = $this->formatLimesurveyResponse($response, array('token'));

    // save limesurvey raw data
    $ls_raw_data = new stdClass();
    $ls_raw_data->participants = $formatResponse;
    $ls_raw_data->sid = $iSurveyID; 
    
    $fqp->setLimesurveyDataRaw($this->serializer->encode($ls_raw_data));
    
  }

  /**
   * Helper function to extract fields from LimeSurvey response
   *
   * @param $response array, successful response array from LimeSurvey
   * @param $fields array, field name that need to be extracted from response
   * @return array, extracted fields list with the value
   */
  private function formatLimesurveyResponse($response, $fields )
  {
    $data = array();

    foreach ($response as $r) {

      foreach ($fields as $field) {
        $data[$field][] = isset($r[$field]) ? $r[$field] : '';
      }

    }

    return $data;
  }

}
