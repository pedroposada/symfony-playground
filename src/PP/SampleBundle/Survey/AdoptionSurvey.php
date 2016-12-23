<?php
/**
 * PP/SampleBundle/Survey/AdoptionSurvey.php
 * 
 * Adoption Survey Class
 * Handles all business logic for an Adoption survey
 * Extends the Lime Survey class
 * 
 * @version 1.0
 * 
 */

namespace PP\SampleBundle\Survey;

use PP\SampleBundle\Survey\LimeSurvey;

class AdoptionSurvey extends LimeSurvey
{
  
  protected $market;
  
  protected $specialty;
  
  protected $brands;
  
  protected $statements;
  
  protected $patients;
  
  protected $url_exit;
  
  public function __construct($templating, $survey_data)
  // public function __construct($form_data)
  {
    parent::setTemplating($templating);
    // $this->setTemplating();
    
    $this->market = $survey_data->market;
    $this->specialty = $survey_data->specialty;
    $this->brands = $survey_data->brands;
    $this->statements = $survey_data->statements;
    $this->patients = $survey_data->patients;
    $this->url_exit = $survey_data->url_exit;
  }
  
  /*
  public function setParentTemplating($templating)
  {
    parent::setTemplating($templating);
  }
  */
  
  /**
   * Function to fill the arrays needed for the survey
   */
  public function createSurveyComponants()
  {
    
    $templating = $this->getTemplating();
    
    // ids
    // survey       100000
    // group        2000
    // questions    30000
    // subquestions 40000
    // condition    500
    
// ------------------------------------------------------------------------------
    
    $sid = '896139';
    
    // Survey settings
    
    $survey_row = $templating->render('PPSampleBundle:limesurvey:limesurveySurveyRow.xml.twig', 
      array('survey_id' => $sid,
        'adminemail' => 'steven.lee@pslgroup.com',
        'faxto' => '5149999999',
        'bounce_email' => 'steven.lee@pslgroup.com',
      )
    );
    $this->addSurvey($survey_row);
    
    // Language settings 
    
    $survey_language_settings_row = $templating->render('PPSampleBundle:limesurvey:AdoptionSurveyAssets/adoptionSurveyLanguageSettings.xml.twig',
      array('surveyls_survey_id' => $sid,
        'surveyls_url' => $this->url_exit,
      )
    );
    $this->addSurveysLanguagesetting($survey_language_settings_row);
    
    // url parameters
    
    $survey_url_parameters_row = $templating->render('PPSampleBundle:limesurvey:limesurveyUrlParameters.xml.twig', 
      array('id' => '100',
        'sid' => $sid,
        'parameter' => 'lstoken',
        'targetqid' => '',
        'targetsqid' => '',
      )
    );
    $this->addSurveyUrlParameter($survey_url_parameters_row);

// ---------------------------------------------------------------------------------------------------------------------------
// group 0

// Spontaneous 
// ---------------------------------------------------------------------------------------------------------------------------

    $group_order = 0;

    $gid_0 = 2000;
    
    $group_0_row = $templating->render('PPSampleBundle:limesurvey:limesurveyGroupRow.xml.twig', 
      array('gid' => $gid_0,
        'sid' => $sid,
        'group_name' => 'Spontaneous',
        'group_order' => $group_order,
      )
    );
    
    $this->addGroup($group_0_row);
    $group_order++;

    // ------------------------------------------------------------------------------
    // group 0
    // question 0
    // Thinking about the treatment of your {PATIENT} which drugs come to mind?
    // Please enter only one drug per box.
    // ------------------------------------------------------------------------------

    $qid_0_0 = 30000;
    $question_0_0 = '<p>Thinking about the treatment of your ' . $this->patients . ', which drugs come to mind? Please enter only one drug per box.</p>';
   
    $question_0_0_row = $templating->render('PPSampleBundle:limesurvey:limesurveyQuestionRow.xml.twig', 
      array('qid' => $qid_0_0,
        'parent_qid' => 0,
        'sid' => $sid,
        'gid' => $gid_0,
        'type' => 'Q',
        'title' => 'G001Q001',
        'question' => $question_0_0,
        'help' => '',
        'preg' => '',
        'other' => 'N',
        'mandatory' => 'N',
        'question_order' => 0,
        'scale_id' => 0,  
        'same_default' => 0,
        'relevance' => 1,
      )
    );
    
    $this->addQuestion($question_0_0_row);

    // ------------------------------------------------------------------------------
    // group 0
    // question 0
    // subquestion
    // Drugs
    // ------------------------------------------------------------------------------
    
    $subquestions_0_0 = 40000;
    
    for ($index=1; $index<=10; $index++) {
      $subquestion_row = $templating->render('PPSampleBundle:limesurvey:limesurveySubquestionRow.xml.twig', 
        array('qid' => $subquestions_0_0 + $index,
          'parent_qid' => $qid_0_0,
          'sid' => $sid,
          'gid' => $gid_0,
          'type' => 'Q',
          'title' => 'SQ00' . $index,
          'question' => $index,
          'other' => 'N',
          'question_order' => $index,
          'scale_id' => 0,
        )
      );
      
      $this->addSubquestion($subquestion_row);
    }

// ---------------------------------------------------------------------------------------------------------------------------
// group 1

// Adoption
// ---------------------------------------------------------------------------------------------------------------------------
    $gid_1 = 2100;
    
    $group_1_row = $templating->render('PPSampleBundle:limesurvey:limesurveyGroupRow.xml.twig', 
      array('gid' => $gid_1,
        'sid' => $sid,
        'group_name' => 'Adoption',
        'group_order' => $group_order,
      )
    );
    
    $this->addGroup($group_1_row);
    $group_order++;

    // ------------------------------------------------------------------------------
    // group 1
    // question 0
    // Thinking about the following drugs used to treat {PATIENT}, which of these statements applies to each of them?
    // Please select one response for each drug.
    // ------------------------------------------------------------------------------

    $qid_1_0 = 30100;
    $question_1_0 = '<p>Thinking about the following drugs used to treat ' . $this->patients . ', which of these statements applies to each of them? Please select one response for each drug.</p>';
    
    $question_1_0_row = $templating->render('PPSampleBundle:limesurvey:limesurveyQuestionRow.xml.twig', 
      array('qid' => $qid_1_0,
        'parent_qid' => 0,
        'sid' => $sid,
        'gid' => $gid_1,
        'type' => 'F',
        'title' => 'G002Q001',
        'question' => $question_1_0,
        'help' => '',
        'preg' => '',
        'other' => 'N',
        'mandatory' => 'Y',
        'question_order' => 0,
        'scale_id' => 0,
        'same_default' => 0,
        'relevance' => 1,
      )
    );
    
    $this->addQuestion($question_1_0_row);

    // ------------------------------------------------------------------------------
    // group 1
    // question 0
    // answers
    // ------------------------------------------------------------------------------
    
    $answers_1_0 = array(
      'I currently use this drug',
      'I have used this drug previously and would consider using it again',
      'I have used this drug previously but would no longer consider it',
      'I have never used this drug but would consider it',
      'I have never used this drug and would not consider it',
      'I am not aware of this drug',
    );
    foreach ($answers_1_0 as $key => $answer) {
      $index = $key + 1;
      $answer_row = $templating->render('PPSampleBundle:limesurvey:limesurveyAnswerRow.xml.twig', 
        array('qid' => $qid_1_0,
          'code' => 'A0' . $index,
          'answer' => $answer,
          'sortorder' => $index,
          'assessment_value' => 0,
        )
      );
      
      $this->addAnswer($answer_row);
    }

    // ------------------------------------------------------------------------------
    // group 1
    // question 0
    // subquestion
    // Brands
    // ------------------------------------------------------------------------------
    
    $subquestions_1_0 = 40100;
    
    foreach ($this->brands as $key => $brand) {
      $index = $key + 1;
      $subquestion_row = $templating->render('PPSampleBundle:limesurvey:limesurveySubquestionRow.xml.twig', 
        array('qid' => $subquestions_1_0 + $index,
          'parent_qid' => $qid_1_0,
          'sid' => $sid,
          'gid' => $gid_1,
          'type' => 'F',
          'title' => 'SQ00' . $index,
          'question' => $brand,
          'other' => 'N',
          'question_order' => $index,
          'scale_id' => 0,
        )
      );
      
      $this->addSubquestion($subquestion_row);
    }

    // ------------------------------------------------------------------------------
    // group 1
    // question 0
    // question_attributes
    // ------------------------------------------------------------------------------
    
    $question_attribute_1_0_row_0 = $templating->render('PPSampleBundle:limesurvey:limesurveyQuestionAttributeRow.xml.twig', 
      array('qid' => $qid_1_0,
        'attribute' => 'printable_help',
        'value' => 1,
        'language' => 'EN',
      ));
    
    $this->addQuestionAttribute($question_attribute_1_0_row_0);
    
    $question_attribute_0_0_row_1 = $templating->render('PPSampleBundle:limesurvey:limesurveyQuestionAttributeRow.xml.twig', 
      array('qid' => $qid_1_0,
        'attribute' => 'random_order',
        'value' => 1,
        'language' => '',
      ));
    
    $this->addQuestionAttribute($question_attribute_1_0_row_0);


    return $this;
  }
}
  
