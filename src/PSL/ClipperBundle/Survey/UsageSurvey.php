<?php
/**
 * PSL/ClipperBundle/Survey/NPSPlusSurvey.php
 * 
 * NPS+ Survey Class
 * Handles all business logic for an NPS+ survey
 * Extends the Lime Survey class
 * 
 * @version 1.0
 * 
 */

namespace PSL\ClipperBundle\Survey;

use PSL\ClipperBundle\Survey\LimeSurvey;

class UsageSurvey extends LimeSurvey
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
    
    $survey_row = $templating->render('PSLClipperBundle:limesurvey:limesurveySurveyRow.xml.twig', 
      array('survey_id' => $sid,
        'adminemail' => 'steven.lee@pslgroup.com',
        'faxto' => '5149999999',
        'bounce_email' => 'steven.lee@pslgroup.com',
      )
    );
    $this->addSurvey($survey_row);
    
    // Language settings 
    
    $survey_language_settings_row = $templating->render('PSLClipperBundle:limesurvey:NPSSurveyAssets/npsSurveyLanguageSettings.xml.twig',
      array('surveyls_survey_id' => $sid,
        'surveyls_url' => $this->url_exit,
      )
    );
    $this->addSurveysLanguagesetting($survey_language_settings_row);
    
    // url parameters
    
    $survey_url_parameters_row = $templating->render('PSLClipperBundle:limesurvey:limesurveyUrlParameters.xml.twig', 
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

// Adoption
// ---------------------------------------------------------------------------------------------------------------------------
    
    $group_order = 0;

    $gid_0 = 2000;
    
    $group_0_row = $templating->render('PSLClipperBundle:limesurvey:limesurveyGroupRow.xml.twig', 
      array('gid' => $gid_0,
        'sid' => $sid,
        'group_name' => 'Adoption',
        'group_order' => $group_order,
      )
    );
    
    $this->addGroup($group_0_row);
    $group_order++;
    
    // ------------------------------------------------------------------------------
    // group 0
    // question 0
    // Thinking about the following drugs used to treat {PATIENT}, which of these statements applies to each of them? 
    // Please select one response for each drug.
    // ------------------------------------------------------------------------------
    
    $qid_0_0 = 30000;
    $question_0_0 = '<p>Thinking about the following drugs used to treat ' . $this->patients . ', which of these statements applies to each of them? Please select one response for each drug.</p>';
    
    $question_0_0_row = $templating->render('PSLClipperBundle:limesurvey:limesurveyQuestionRow.xml.twig', 
      array('qid' => $qid_0_0,
        'parent_qid' => 0,
        'sid' => $sid,
        'gid' => $gid_0,
        'type' => 'F',
        'title' => 'G001Q001',
        'question' => $question_0_0,
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
    
    $this->addQuestion($question_0_0_row);
    
    
    // ------------------------------------------------------------------------------
    // group 0
    // question 0
    // answers
    // ------------------------------------------------------------------------------
    
    $answers_0_0 = array(
      'I currently use this drug',
      'I have used this drug previously and would consider using it again',
      'I have used this drug previously but would no longer consider it',
      'I have never used this drug but would consider it',
      'I have never used this drug and would not consider it',
      'I am not aware of this drug',
    );
    foreach ($answers_0_0 as $key => $answer) {
      $index = $key + 1;
      $answer_row = $templating->render('PSLClipperBundle:limesurvey:limesurveyAnswerRow.xml.twig', 
        array('qid' => $qid_0_0,
          'code' => 'A0' . $index,
          'answer' => $answer,
          'sortorder' => $index,
          'assessment_value' => 0,
        )
      );
      
      $this->addAnswer($answer_row);
    }

    // ------------------------------------------------------------------------------
    // group 0
    // question 0
    // subquestion
    // Brands
    // ------------------------------------------------------------------------------
    
    $subquestions_0_0 = 40000;
    
    foreach ($this->brands as $key => $brand) {
      $index = $key + 1;
      $subquestion_row = $templating->render('PSLClipperBundle:limesurvey:limesurveySubquestionRow.xml.twig', 
        array('qid' => $subquestions_0_0 + $index,
          'parent_qid' => $qid_0_0,
          'sid' => $sid,
          'gid' => $gid_0,
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
    // group 0
    // question 0
    // question_attributes
    // ------------------------------------------------------------------------------
    
    $question_attribute_0_0_row_0 = $templating->render('PSLClipperBundle:limesurvey:limesurveyQuestionAttributeRow.xml.twig', 
      array('qid' => $qid_0_0,
        'attribute' => 'printable_help',
        'value' => 1,
        'language' => 'EN',
      ));
    
    $this->addQuestionAttribute($question_attribute_0_0_row_0);
    
    $question_attribute_0_0_row_1 = $templating->render('PSLClipperBundle:limesurvey:limesurveyQuestionAttributeRow.xml.twig', 
      array('qid' => $qid_0_0,
        'attribute' => 'random_order',
        'value' => 1,
        'language' => '',
      ));
    
    $this->addQuestionAttribute($question_attribute_0_0_row_1);
    
    
// ---------------------------------------------------------------------------------------------------------------------------
    
    // ------------------------------------------------------------------------------
    // group 0
    // question 1
    // Currently used (hidden)
    // hidden
    // ------------------------------------------------------------------------------
    
    $qid_0_1 = 300100;
    $question_0_1 = 'Currently used (hidden)
<script type="text/javascript" charset="utf-8">
$(document).ready(function(){
  $("#question{QID}").hide();
  $(".question-wrapper input:radio").change(function(){
    var name = $(this).attr("name");
    var n = name.indexOf("SQ");
    var subQuestion = name.substring(n);
    var answer = $(this).attr("value");
    if (answer == "A01") {
      $("#java{SGQ}" + subQuestion).val("A1");
      $("#answer{SGQ}" + subQuestion + "-A1").attr("checked", "checked");
      $("#answer{SGQ}" + subQuestion + "-").attr("checked", false);
    }
    else {
      $("#java{SGQ}" + subQuestion).val("");
      $("#answer{SGQ}" + subQuestion + "-A1").attr("checked", false);
      $("#answer{SGQ}" + subQuestion + "-").attr("checked", "checked");
    }
  });
});
</script>';
    
    $question_0_1_row = $templating->render('PSLClipperBundle:limesurvey:limesurveyQuestionRow.xml.twig', 
      array('qid' => $qid_0_1,
        'parent_qid' => 0,
        'sid' => $sid,
        'gid' => $gid_0,
        'type' => 'F',
        'title' => 'G001Q002',
        'question' => $question_0_1,
        'help' => '',
        'preg' => '',
        'other' => 'N',
        'mandatory' => 'N',
        'question_order' => 4,
        'scale_id' => 0,
        'same_default' => 0,
        'relevance' => 1,
      )
    );
    
    $this->addQuestion($question_0_1_row);
    
    // ------------------------------------------------------------------------------
    // group 0
    // question 1
    // answers
    // ------------------------------------------------------------------------------
    
    $answer_0_1_row = $templating->render('PSLClipperBundle:limesurvey:limesurveyAnswerRow.xml.twig', 
      array('qid' => $qid_0_1,
        'code' => 'A1',
        'answer' => 'yes',
        'sortorder' => 1,
        'assessment_value' => 0,
      )
    );
    $this->addAnswer($answer_0_1_row);
    
    // ------------------------------------------------------------------------------
    // group 0
    // question 1
    // subquestions
    // ------------------------------------------------------------------------------
    
    $subquestions_0_1 = 40100;
    
    foreach ($this->brands as $key => $brand) {
      $index = $key + 1;
      $subquestion_row = $templating->render('PSLClipperBundle:limesurvey:limesurveySubquestionRow.xml.twig', 
        array('qid' => $subquestions_0_1 + $index,
          'parent_qid' => $qid_0_1,
          'sid' => $sid,
          'gid' => $gid_0,
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
    
    
// ---------------------------------------------------------------------------------------------------------------------------

    // ------------------------------------------------------------------------------
    // group 0
    // question 2
    // Brand awareness (hidden)
    // hidden
    // ------------------------------------------------------------------------------

    $qid_0_2 = 300200;
    $question_0_2 = 'Brand awareness (hidden)
<script type="text/javascript" charset="utf-8">
$(document).ready(function(){
  $("#question{QID}").hide();
  $(".question-wrapper input:radio").change(function(){
    var name = $(this).attr("name");
    var n = name.indexOf("SQ");
    var subQuestion = name.substring(n);
    var answer = $(this).attr("value");
    if (answer != "A06") {
      $("#java{SGQ}" + subQuestion).val("A1");
      $("#answer{SGQ}" + subQuestion + "-A1").attr("checked", "checked");
      $("#answer{SGQ}" + subQuestion + "-").attr("checked", false);
    }
    else {
      $("#java{SGQ}" + subQuestion).val("");
      $("#answer{SGQ}" + subQuestion + "-A1").attr("checked", false);
      $("#answer{SGQ}" + subQuestion + "-").attr("checked", "checked");
    }
  });
});
</script>';
    
    $question_0_2_row = $templating->render('PSLClipperBundle:limesurvey:limesurveyQuestionRow.xml.twig', 
      array('qid' => $qid_0_2,
        'parent_qid' => 0,
        'sid' => $sid,
        'gid' => $gid_0,
        'type' => 'F',
        'title' => 'G001Q003',
        'question' => $question_0_2,
        'help' => '',
        'preg' => '',
        'other' => 'N',
        'mandatory' => 'N',
        'question_order' => 5,
        'scale_id' => 0,
        'same_default' => 0,
        'relevance' => 1,
      )
    );
    
    $this->addQuestion($question_0_2_row);
    
    // ------------------------------------------------------------------------------
    // group 0
    // question 1
    // answers
    // ------------------------------------------------------------------------------
    
    $answer_0_2_row = $templating->render('PSLClipperBundle:limesurvey:limesurveyAnswerRow.xml.twig', 
      array('qid' => $qid_0_2,
        'code' => 'A1',
        'answer' => 'yes',
        'sortorder' => 1,
        'assessment_value' => 0,
      )
    );
    $this->addAnswer($answer_0_2_row);
    
    // ------------------------------------------------------------------------------
    // group 0
    // question 1
    // subquestions
    // ------------------------------------------------------------------------------
    
    $subquestions_0_2 = 40200;
    
    foreach ($this->brands as $key => $brand) {
      $index = $key + 1;
      $subquestion_row = $templating->render('PSLClipperBundle:limesurvey:limesurveySubquestionRow.xml.twig', 
        array('qid' => $subquestions_0_2 + $index,
          'parent_qid' => $qid_0_2,
          'sid' => $sid,
          'gid' => $gid_0,
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

// ---------------------------------------------------------------------------------------------------------------------------

    // ------------------------------------------------------------------------------
    // group 0
    // question 3
    // Brand awareness 2 (hidden)
    // hidden
    // ------------------------------------------------------------------------------

    $qid_0_3 = 300300;

    $question_0_3 = "Brand awareness 2 (hidden) 
<script type='text/javascript' charset='utf-8'>
$(document).ready(function(){
    $('#question{QID}').hide();
    
    $('.question-wrapper input:radio').change(function(){
        
        var name = $(this).attr('name');
        var n = name.indexOf('SQ');
        var subQuestion = name.substring(n);
        var answer = $(this).attr('value');

        if (answer != 'A03' && answer != 'A05' && answer != 'A06') {
            $('#java{SGQ}' + subQuestion).val('A1');
            $('#answer{SGQ}' + subQuestion + '-A1').attr('checked', 'checked');
            $('#answer{SGQ}' + subQuestion + '-').attr('checked', false);
        }
        else {
            $('#java{SGQ}' + subQuestion).val('');
            $('#answer{SGQ}' + subQuestion + '-A1').attr('checked', false);
            $('#answer{SGQ}' + subQuestion + '-').attr('checked', 'checked');
        }
    });
});
</script>";

    $question_0_3_row = $templating->render('PSLClipperBundle:limesurvey:limesurveyQuestionRow.xml.twig', 
      array('qid' => $qid_0_3,
        'parent_qid' => 0,
        'sid' => $sid,
        'gid' => $gid_0,
        'type' => 'F',
        'title' => 'G001Q004',
        'question' => $question_0_3,
        'help' => '',
        'preg' => '',
        'other' => 'N',
        'mandatory' => 'N',
        'question_order' => 6,
        'scale_id' => 0,
        'same_default' => 0,
        'relevance' => 1,
      )
    );
    
    $this->addQuestion($question_0_3_row);
    
    // ------------------------------------------------------------------------------
    // group 0
    // question 1
    // answers
    // ------------------------------------------------------------------------------
    
    $answer_0_3_row = $templating->render('PSLClipperBundle:limesurvey:limesurveyAnswerRow.xml.twig', 
      array('qid' => $qid_0_3,
        'code' => 'A1',
        'answer' => 'yes',
        'sortorder' => 1,
        'assessment_value' => 0,
      )
    );
    $this->addAnswer($answer_0_3_row);
    
    // ------------------------------------------------------------------------------
    // group 0
    // question 1
    // subquestions
    // ------------------------------------------------------------------------------
    
    $subquestions_0_3 = 40300;
    
    foreach ($this->brands as $key => $brand) {
      $index = $key + 1;
      $subquestion_row = $templating->render('PSLClipperBundle:limesurvey:limesurveySubquestionRow.xml.twig', 
        array('qid' => $subquestions_0_3 + $index,
          'parent_qid' => $qid_0_3,
          'sid' => $sid,
          'gid' => $gid_0,
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

// ---------------------------------------------------------------------------------------------------------------------------
// group 1

// current prescribing
// ---------------------------------------------------------------------------------------------------------------------------
    
    $gid_1 = 2100;
    
    $group_1_row = $templating->render('PSLClipperBundle:limesurvey:limesurveyGroupRow.xml.twig', 
      array('gid' => $gid_1,
        'sid' => $sid,
        'group_name' => 'Current prescribing',
        'group_order' => $group_order,
      )
    );
    
    $this->addGroup($group_1_row);
    $group_order++;
    
    // ------------------------------------------------------------------------------
    // group 1
    // question 0
    // In a typical month what % of your patients for the treatment of {PATIENT} do you prescribe each of the following drugs? 
    // We realise it is not possible to know this exactly, but we would sincerely appreciate your best approximation. 
    // Please express your answer as a percentage. Your responses must add up to at least 100% and can be more if drugs are co-prescribed.
    // ------------------------------------------------------------------------------
    
    $qid_1_0 = 30100;
    $question_1_0 = '<p>In a typical month what % of your patients for the treatment of ' . $this->patients . ' do you prescribe each of the following drugs? We realise it is not possible to know this exactly, but we would sincerely appreciate your best approximation. Please express your answer as a percentage. Your responses must add up to at least 100% and can be more if drugs are co-prescribed.</p>';
    $help_1_0 = '<script type="text/javascript" charset="utf-8">
$(document).ready(function(){
  var tbl = $("table.questions-list tbody");
  var total_percentage = $("table.questions-list tr.subquestions-list").not("[style*=\"display\"]").first().clone().attr("id", "total-percentage");
  total_percentage.find("th").text("Total");
  total_percentage.find("input").attr("disabled", "true");
  total_percentage.find("input").addClass("readonly");
  tbl.append(total_percentage);
  $("input").not(".readonly, input[type=\"hidden\"]").on("input", function(e) { 
      var percentage = 0;
      var newval = $(this).val().replace(/[^0-9.]/g, "");
      if( parseInt(newval, 10) > 100 ) { 
          newval = newval.substring(0, 2); 
      }
      $(this).val(newval);
      $("input").not(".readonly, input[type=\"hidden\"]").each(function(index,element){
          var elemVal = parseInt($(element).val(), 10) || 0;
          percentage += elemVal; 
      });
      total_percentage.find("input.readonly").val(percentage);
  });
});
</script>';
    
    $question_1_0_row = $templating->render('PSLClipperBundle:limesurvey:limesurveyQuestionRow.xml.twig', 
      array('qid' => $qid_1_0,
        'parent_qid' => 0,
        'sid' => $sid,
        'gid' => $gid_1,
        'type' => ':',
        'title' => 'G002Q001',
        'question' => $question_1_0,
        'help' => $help_1_0,
        'preg' => '',
        'other' => 'N',
        'mandatory' => 'N',
        'question_order' => 1,
        'scale_id' => 0,
        'same_default' => 0,
        'relevance' => 1,
      )
    );
    
    $this->addQuestion($question_1_0_row);
    
    
    // ------------------------------------------------------------------------------
    // group 1
    // question 0
    // subquestions
    // ------------------------------------------------------------------------------
    
    $subquestions_1_0 = 40400;
    
    // top row
    $subquestion_1_0_row = $templating->render('PSLClipperBundle:limesurvey:limesurveySubquestionRow.xml.twig', 
      array('qid' => $subquestions_1_0,
        'parent_qid' => $qid_1_0,
        'sid' => $sid,
        'gid' => $gid_1,
        'type' => 'T',
        'title' => 'SQ001',
        'question' => '%',
        'other' => 'N',
        'question_order' => 0,
        'scale_id' => 1,
      )
    );
      
    $this->addSubquestion($subquestion_1_0_row);
    
    // loop through all brands
    foreach ($this->brands as $key => $brand) {
      $index = $key + 1;
      $subquestion_row = $templating->render('PSLClipperBundle:limesurvey:limesurveySubquestionRow.xml.twig', 
        array('qid' => $subquestions_1_0 + $index,
          'parent_qid' => $qid_1_0,
          'sid' => $sid,
          'gid' => $gid_1,
          'type' => 'T',
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
    // attributes
    // ------------------------------------------------------------------------------
    
    $attributes_1_0_row_0 = $templating->render('PSLClipperBundle:limesurvey:limesurveyQuestionAttributeRow.xml.twig', 
      array('qid' => $qid_1_0,
        'attribute' => 'array_filter',
        'value' => 'G001Q002',
        'language' => '',
      ));
    
    $this->addQuestionAttribute($attributes_1_0_row_0);
    
    $attributes_1_0_row_1 = $templating->render('PSLClipperBundle:limesurvey:limesurveyQuestionAttributeRow.xml.twig', 
      array('qid' => $qid_1_0,
        'attribute' => 'input_boxes',
        'value' => 1,
        'language' => '',
      ));
    
    $this->addQuestionAttribute($attributes_1_0_row_1);
    
    $attributes_1_0_row_2 = $templating->render('PSLClipperBundle:limesurvey:limesurveyQuestionAttributeRow.xml.twig', 
      array('qid' => $qid_1_0,
        'attribute' => 'printable_help',
        'value' => 1,
        'language' => 'en',
      ));
    
    $this->addQuestionAttribute($attributes_1_0_row_2);

// ---------------------------------------------------------------------------------------------------------------------------
// group 2

// future prescribing
// ---------------------------------------------------------------------------------------------------------------------------
    
    $gid_2 = 2200;
    
    $group_2_row = $templating->render('PSLClipperBundle:limesurvey:limesurveyGroupRow.xml.twig', 
      array('gid' => $gid_2,
        'sid' => $sid,
        'group_name' => 'Future prescribing',
        'group_order' => $group_order,
      )
    );
    
    $this->addGroup($group_2_row);
    $group_order++;
    
    // ------------------------------------------------------------------------------
    // group 2
    // question 0
    // We are interested in your opinion of how your prescribing might change in the next 12 months. 
    // If we were to ask you the previous question 12 months from now, how do you think your responses might change? 
    // We realise it is not possible to know this exactly, but we would sincerely appreciate your best approximation. 
    // The sum of your responses should add up to at least 100% and can be more if drugs are co-prescribed.
    // ------------------------------------------------------------------------------
    
    $qid_2_0 = 30200;
    $question_2_0 = '<p>We are interested in your opinion of how your prescribing might change in the next 12 months. If we were to ask you the previous question 12 months from now, how do you think your responses might change?</p>';
    $question_2_0.= '<p>We realise it is not possible to know this exactly, but we would sincerely appreciate your best approximation. The sum of your responses should add up to at least 100% and can be more if drugs are co-prescribed.</p>';
    
    // Javascript block
    // We need to construct limesurvey token because the token doesn't 
    // seem like able to use dynamic value. 
    // eg. {G002Q001_SQ001_SQ001.shown}
    $js_2_0_block = '';
    foreach ($this->brands as $key => $brand) {
      $index = $key + 1;

      $js_2_0_block .= "
      var target_input".$index." = sid+'X'+gid+'X'+qid+'SQ00".$index."_SQ001';
      var brand_value".$index." = '{G002Q001_SQ00" .$index. "_SQ001.shown}';
      if (brand_value".$index." =='' ){
        brand_value".$index." = 0;
        $('#answer'+target_input".$index.").attr('disabled', 'true');
        $('#answer'+target_input".$index.").addClass('readonly');
      }
      brand_value".$index." = parseInt(brand_value".$index.", 10);
      total_percentage_value += brand_value" . $index. ";
      $('#answer'+target_input".$index.").val(brand_value".$index.");
      $('#java'+target_input".$index.").val(brand_value".$index.");
      ";
    }

    $help_2_0 = '<script type="text/javascript" charset="utf-8">
(function ($) {
  $(document).ready(function () {
        
    // construct current question prefix
    var sid = "{SID}";
    var gid = "{GID}";
    var qid = "{QID}";
    var total_percentage_value = 0;
    ' . $js_2_0_block . '

    // Append total percentage field.
    var tbl = $("table.questions-list tbody");
    var total_percentage = $("table.questions-list tr.subquestions-list").not("[style*=\"display\"]").first().clone().attr("id", "total-percentage");
    total_percentage.find("th").text("Total");
    total_percentage.find("input").attr("disabled", "true");
    total_percentage.find("input").addClass("readonly");
    total_percentage.find("#answer"+target_input1).val(total_percentage_value);
    total_percentage.find("#java"+target_input1).val(total_percentage_value);
    tbl.append(total_percentage);

    var target_input1_2 = sid + "X" + gid + "X" + qid + "SQ001_SQ002";
    
    $("input").not(".readonly, input[type=\"hidden\"]").on("input", function(e) { 
      var percentage1 = 0;
      var percentage2 = 0;
      var newval = $(this).val().replace(/[^0-9.]/g, "");
      if( parseInt(newval, 10) > 100 ) { 
          newval = newval.substring(0, 2); 
      }
      $(this).val(newval);

      $("input[name$=\"SQ001\"]").not(".readonly, input[type=\"hidden\"]").each(function(index,element){
          var elemVal = parseInt($(element).val(), 10) || 0;
          percentage1 += elemVal; 
      });

      $("input[name$=\"SQ002\"]").not(".readonly, input[type=\"hidden\"]").each(function(index,element){
          var elemVal = parseInt($(element).val(), 10) || 0;
          percentage2 += elemVal; 
      });

      total_percentage.find("#answer" + target_input1).val(percentage1);
      total_percentage.find("#java" + target_input1).val(percentage1);

      total_percentage.find("#answer" + target_input1_2).val(percentage2);
      total_percentage.find("#java" + target_input1_2).val(percentage2);
      
    });

  });
 })(jQuery);
</script>';

    $question_2_0_row = $templating->render('PSLClipperBundle:limesurvey:limesurveyQuestionRow.xml.twig', 
      array('qid' => $qid_2_0,
        'parent_qid' => 0,
        'sid' => $sid,
        'gid' => $gid_2,
        'type' => ':',
        'title' => 'G003Q001',
        'question' => $question_2_0,
        'help' => $help_2_0,
        'preg' => '',
        'other' => 'N',
        'mandatory' => 'N',
        'question_order' => 1,
        'scale_id' => 0,
        'same_default' => 0,
        'relevance' => 1,
      )
    );
    
    $this->addQuestion($question_2_0_row);
    
    
    // ------------------------------------------------------------------------------
    // group 2
    // question 0
    // subquestions
    // ------------------------------------------------------------------------------
    
    $subquestions_2_0 = 40500;
    
    // top row
    $subquestion_2_0_row = $templating->render('PSLClipperBundle:limesurvey:limesurveySubquestionRow.xml.twig', 
      array('qid' => $subquestions_2_0,
        'parent_qid' => $qid_2_0,
        'sid' => $sid,
        'gid' => $gid_2,
        'type' => 'T',
        'title' => 'SQ001',
        'question' => 'Current prescribing in a typical month',
        'other' => 'N',
        'question_order' => 0,
        'scale_id' => 1,
      )
    );
      
    $this->addSubquestion($subquestion_2_0_row);

    $subquestions_2_1 = 40600;

    $subquestion_2_1_row = $templating->render('PSLClipperBundle:limesurvey:limesurveySubquestionRow.xml.twig', 
      array('qid' => $subquestions_2_1,
        'parent_qid' => $qid_2_0,
        'sid' => $sid,
        'gid' => $gid_2,
        'type' => 'T',
        'title' => 'SQ002',
        'question' => '1 year from now',
        'other' => 'N',
        'question_order' => 0,
        'scale_id' => 1,
      )
    );
      
    $this->addSubquestion($subquestion_2_1_row);

    // loop through all brands
    foreach ($this->brands as $key => $brand) {
      $index = $key + 1;
      $subquestion_row = $templating->render('PSLClipperBundle:limesurvey:limesurveySubquestionRow.xml.twig', 
        array('qid' => $subquestions_2_1 + $index,
          'parent_qid' => $qid_2_0,
          'sid' => $sid,
          'gid' => $gid_2,
          'type' => 'T',
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
    // group 2
    // question 0
    // attributes
    // ------------------------------------------------------------------------------
    
    $attributes_2_0_row_0 = $templating->render('PSLClipperBundle:limesurvey:limesurveyQuestionAttributeRow.xml.twig', 
      array('qid' => $qid_2_0,
        'attribute' => 'array_filter',
        'value' => 'G001Q004',
        'language' => '',
      ));
    
    $this->addQuestionAttribute($attributes_2_0_row_0);
    
    $attributes_2_0_row_1 = $templating->render('PSLClipperBundle:limesurvey:limesurveyQuestionAttributeRow.xml.twig', 
      array('qid' => $qid_2_0,
        'attribute' => 'input_boxes',
        'value' => 1,
        'language' => '',
      ));
    
    $this->addQuestionAttribute($attributes_2_0_row_1);
    
    $attributes_2_0_row_2 = $templating->render('PSLClipperBundle:limesurvey:limesurveyQuestionAttributeRow.xml.twig', 
      array('qid' => $qid_2_0,
        'attribute' => 'printable_help',
        'value' => 1,
        'language' => 'en',
      ));
    
    $this->addQuestionAttribute($attributes_2_0_row_2);


    return $this;
  }
}
  
