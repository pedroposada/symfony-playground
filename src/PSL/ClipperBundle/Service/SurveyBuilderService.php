<?php
/**
 * Survey Builder Service
 * 
 * Factory class
 */

namespace PSL\ClipperBundle\Service;

// use Symfony\Component\Templating\PhpEngine as PhpEngine;
// use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\DependencyInjection\ContainerInterface;

use PSL\ClipperBundle\Survey\NPSPlusSurvey;
use PSL\ClipperBundle\Survey\AdoptionSurvey;
use PSL\ClipperBundle\Survey\UsageSurvey;
use PSL\ClipperBundle\Survey\ProfilesSurvey;
use PSL\ClipperBundle\Survey\BarriersSurvey;
use PSL\ClipperBundle\Survey\LimeSurvey;

use \stdClass as stdClass;
use \Exception as Exception;

/**
 * Survey Builder Service for Clipper
 */
class SurveyBuilderService
{
  
  protected $container;
  
  // @TODO: only get templating
  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }
  
  /*
  protected $templating;
  
  public function __construct()
  {
    
  }
  
  // public function setTemplating(Templating $templating)
  public function setTemplating()
  {
    // $templating = new PhpEngine(new TemplateNameParser());
    // $this->templating = $templating;
  }
  */
  
  /**
   *
   */
  public function createSurvey($type, $survey_data)
  {
    
    $survey_output = '';
    
    switch ($type) {
      case 'nps':
        $nps_survey = new NPSPlusSurvey($this->container->get('templating'), $survey_data);
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
