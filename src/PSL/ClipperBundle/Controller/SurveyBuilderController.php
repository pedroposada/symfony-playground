<?php
/**
 * Survey Builder Service
 * 
 * Factory class
 */

namespace PSL\ClipperBundle\Controller;

use \stdClass as stdClass;
use \Exception as Exception;

/**
 * Survey Builder Service for Clipper
 */
class SurveyBuilderService
{
  
  public function __construct()
  {
    
  }
  
  public function createSurvey($type, $form_data)
  {
    // prepare survey data
    // $survey_data
    
    $survey_output = '';
    
    switch ($type) {
      case 'nps':
          // $nps_survey = new NPSSurveyClass($survey_data);
          $survey_output = 'NPS+';
        break;
      case 'adoption':
          // $adoption_survey = new AdoptionSurveyClass($survey_data);
          $survey_output = 'Adoption';
        break;
      default:
        break;
    }
    
    return 'Survey = ' . $survey_output;
  }
}
