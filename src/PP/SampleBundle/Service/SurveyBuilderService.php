<?php
/**
 * Survey Builder Service
 * 
 * Factory class
 */

namespace PP\SampleBundle\Service;

// use Symfony\Component\Templating\PhpEngine as PhpEngine;
// use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\DependencyInjection\ContainerInterface;

use PP\SampleBundle\Survey\NPSPlusSurvey;
use PP\SampleBundle\Survey\AdoptionSurvey;
use PP\SampleBundle\Survey\UsageSurvey;
use PP\SampleBundle\Survey\ProfilesSurvey;
use PP\SampleBundle\Survey\BarriersSurvey;
use PP\SampleBundle\Survey\LimeSurvey;

use \stdClass as stdClass;
use \Exception as Exception;

/**
 * Survey Builder Service for Clipper
 */
class SurveyBuilderService
{
  
  protected $container;
  
  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }
  
  /**
   * Create survey and outputs a LimeSurvey ready XML string
   * 
   * @param string $type - the machine name of survey
   * @param mixed $survey_data - the data needed to build the survey
   * 
   * @return string
   */
  public function createSurvey($type, $survey_data)
  {
    
    $survey_output = '';
    
    switch ($type) {
      case 'nps_plus':
        $nps_survey = new NPSPlusSurvey($this->container, $survey_data);
        $survey_output = $nps_survey->createSurveyComponants()->assembleSurvey();
        // 
        break;
      case 'adoption':
        $adoption_survey = new AdoptionSurvey($this->container->get('templating'), $survey_data);
        $survey_output = $adoption_survey->createSurveyComponants()->assembleSurvey();
        break;
      case 'usage':
        $usage_survey = new UsageSurvey($this->container->get('templating'), $survey_data);
        $survey_output = $usage_survey->createSurveyComponants()->assembleSurvey();
      case 'profiles':
        $profiles_survey = new ProfilesSurvey($this->container->get('templating'), $survey_data);
        $survey_output = $profiles_survey->createSurveyComponants()->assembleSurvey();
      case 'barriers':
        $barriers_survey = new BarriersSurvey($this->container->get('templating'), $survey_data);
        $survey_output = $barriers_survey->createSurveyComponants()->assembleSurvey();
      default:
        break;
    }
    
    return $survey_output;
  }
}
