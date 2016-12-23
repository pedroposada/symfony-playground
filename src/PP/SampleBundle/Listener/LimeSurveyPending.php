<?php

namespace PP\SampleBundle\Listener;

use \Exception;
use \stdClass;

use PP\SampleBundle\Listener\FqProcess;
use PP\SampleBundle\Event\FirstQProjectEvent;
use PP\SampleBundle\Utils\MDMMapping;

class LimeSurveyPending extends FqProcess
{
  
  protected function main(FirstQProjectEvent $event)
  {
    // get FirstQGroup and FirstQProject objects 
    $fqg = $event->getFirstQProjectGroup();
    $fqp = $event->getFirstQProject();
    
    // get LS
    $ls = $this->container->get('limesurvey');
    
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
    
    // import Survey into LS
    $iSurveyID = $ls->import_survey(array(
      'sImportData' => base64_encode($lss), // BASE 64 encoded data of a lss
      'sImportDataType' => 'lss', 
      'sNewSurveyName' => $form_data['title'], 
    ));
    $this->logger->debug("form_data[title] {$form_data['title']}", array('LimeSurveyPending', 'import_survey'));
    if (!is_int($iSurveyID)) {
      throw new Exception("Bad response from LimeSurvey [{$iSurveyID['status']}] on [import_survey]", parent::LOGWARNING);
    }
    $fqp->setLimesurveyDataRaw($this->serializer->encode(array('iSurveyID' => $iSurveyID)));
    $this->logger->debug("iSurveyID: [{$iSurveyID}]", array('import_survey'));
    
    // activate Survey
    $response = $ls->activate_survey(array(
      'iSurveyID' => $iSurveyID, 
    ));
    $this->logger->debug("iSurveyID [{$iSurveyID}]", array('LimeSurveyPending', 'activate_survey'));
    if (!isset($response['status']) || $response['status'] != 'OK') {
      throw new Exception("Bad response from LimeSurvey [{$response['status']}] on [activate_survey]", parent::LOGWARNING);
    }
    
    // activate Tokens
    $response = $ls->activate_tokens(array(
      'iSurveyID' => $iSurveyID, 
    ));
    
    if (!isset($response['status']) || $response['status'] != 'OK') {
      throw new Exception("Bad response from LimeSurvey [{$response['status']}] on [activate_tokens]", parent::LOGWARNING);
    }
    
  }

}
