<?php
/**
 * PSL/ClipperBundle/Survey/LimeSurvey.php
 * 
 * Lime Survey Class
 * This is the base class to handle all common functions when building a survey
 * 
 * @version 1.0
 * 
 */

namespace PSL\ClipperBundle\Survey;

use Symfony\Component\Templating\PhpEngine as PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;

use PSL\ClipperBundle\ClipperEvents;
use PSL\ClipperBundle\Event\LimeSurveyTranslationEvent;

class LimeSurvey
{
  
  protected $container;
  
  protected $answers;
  
  protected $conditions;
  
  protected $groups;
  
  protected $questions;
  
  protected $subquestions;
  
  protected $question_attributes;
  
  protected $surveys;
  
  protected $surveys_languagesettings;
  
  protected $survey_url_parameters;
  
  protected $languages;
  
  protected $type;
  
  public function __construct()
  {
    
  }
  
  /**
   * Function to assemble all arrays created in the subclass
   */
  public function assembleSurvey()
  {
    $elements_array = $this->getElementsArray();
    
    $assembled_survey = $this->container->get('templating')->render('PSLClipperBundle:limesurvey:limesurveyTemplate.xml.twig', $elements_array);
    
    return $assembled_survey;
  }

  /**
   * Function to prepare the elements for the render function 
   */
  public function getElementsArray()
  {
    $elements_array = array('answers' => $this->answers,
        'conditions' => $this->conditions,
        'groups' => $this->groups,
        'questions' => $this->questions,
        'subquestions' => $this->subquestions,
        'question_attributes' => $this->question_attributes,
        'surveys' => $this->surveys,
        'surveys_languagesettings' => $this->surveys_languagesettings,
        'survey_url_parameters' => $this->survey_url_parameters,
        'languages' => $this->languages,
      );
    
    // get dispatcher class
    $dispatcher = $this->getContainer()->get('event_dispatcher');
    // instantiate event object
    $event = new LimeSurveyTranslationEvent($elements_array, $this->languages, $this->type);
    // main event, triggers subscribed listeners 
    $dispatcher->dispatch(ClipperEvents::LIMESURVEY_TRANSLATION, $event);
    
    $elements_array = $event->getElements();
    
    return $elements_array;
  }
  
  /**
   * container
   * 
   * @returns $container 
   */
  public function getContainer()
  {
    return $this->container;
  }
  
  /**
   * container
   * 
   * @returns $container 
   */
  public function setContainer($container)
  {
    $this->container = $container;
  }
  
  // ------------------------------------------------------------------------------------------
  
  /**
   * Add to the answers array 
   *
   * @param object $answers
   */
  public function addAnswer($answer)
  {
    $this->answers[] = $answer;
  }
  
  /**
   * Add to the conditions array 
   *
   * @param object $conditions
   */
  public function addCondition($condition)
  {
    $this->conditions[] = $condition;
  }
  
  /**
   * Add to the groups array 
   *
   * @param object $groups
   */
  public function addGroup($group)
  {
    $this->groups[] = $group;
  }
  
  /**
   * Add to the questions array 
   *
   * @param object $questions
   */
  public function addQuestion($question)
  {
    $this->questions[] = $question;
  }
  
  /**
   * Add to the subquestions array 
   *
   * @param object $subquestions
   */
  public function addSubquestion($subquestion)
  {
    $this->subquestions[] = $subquestion;
  }
  
  /**
   * Add to the question_attributes array 
   *
   * @param object $question_attributes
   */
  public function addQuestionAttribute($question_attribute)
  {
    $this->question_attributes[] = $question_attribute;
  }
  
  /**
   * Add to the surveys array 
   *
   * @param object $surveys
   */
  public function addSurvey($survey)
  {
    $this->surveys[] = $survey;
  }
  
  /**
   * Add to the surveys array 
   *
   * @param object $surveys
   */
  public function addSurveysLanguagesetting($surveys_languagesetting)
  {
    $this->surveys_languagesettings[] = $surveys_languagesetting;
  }
  
  /**
   * Add to the survey_url_parameters array 
   *
   * @param object $survey_url_parameters
   */
  public function addSurveyUrlParameter($survey_url_parameter)
  {
    $this->survey_url_parameters[] = $survey_url_parameter;
  }
  
  /**
   * Languages 
   *
   * @param object $languages
   */
  public function setLanguages($languages)
  {
    $this->languages = $languages;
  }
  
  /**
   * Languages
   *
   * @param object $languages
   */
  public function getLanguages()
  {
    return $this->languages;
  }
  
  /**
   * Type
   * 
   * @param string $type
   */
   public function setType($type)
   {
     $this->type = $type;
   }
}
