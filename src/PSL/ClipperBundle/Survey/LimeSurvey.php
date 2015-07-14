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

class LimeSurvey
{
  
  protected $templating;
  
  protected $answers;
  
  protected $conditions;
  
  protected $groups;
  
  protected $questions;
  
  protected $subquestions;
  
  protected $question_attributes;
  
  protected $surveys;
  
  protected $surveys_languagesettings;
  
  protected $survey_url_parameters;
  
  public function __construct()
  {
    
  }
  
  /**
   * Function to assemble all arrays created in the subclass
   */
  public function assembleSurvey() {
    
    $assembled_survey = $this->templating->render('PSLClipperBundle:limesurvey:limesurveyTemplate.xml.twig', 
      array('answers' => $this->answers,
        'conditions' => $this->conditions,
        'groups' => $this->groups,
        'questions' => $this->questions,
        'subquestions' => $this->subquestions,
        'question_attributes' => $this->question_attributes,
        'surveys' => $this->surveys,
        'surveys_languagesettings' => $this->surveys_languagesettings,
        'survey_url_parameters' => $this->survey_url_parameters,
      )
    );
    
    return $assembled_survey;
  }
  
  /**
   * templating
   * 
   * @returns $templating 
   */
  public function getTemplating() {
    return $this->templating;
  }
  
  /**
   * templating
   * 
   * @returns $templating 
   */
  public function setTemplating($templating) {
    // $templating = new PhpEngine(new TemplateNameParser());
    // $this->templating = $templating;
    $this->templating = $templating;
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
}
