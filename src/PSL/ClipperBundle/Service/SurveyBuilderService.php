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
        // $adoption_survey = new AdoptionSurvey($this->container->get('templating'), $survey_data);
        // $survey_output = $adoption_survey->createSurveyComponants()->assembleSurvey();
        break;
      default:
        break;
    }
    
    return $survey_output;
  }
}
