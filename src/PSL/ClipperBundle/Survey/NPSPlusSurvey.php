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

class NPSPlusSurvey extends LimeSurvey
{

  protected $market;

  protected $specialty;

  protected $brands;

  protected $attributes;

  protected $patients;

  protected $url_exit;

  public function __construct($container, $survey_data)
  {
    $this->setContainer($container);

    $this->market = $survey_data->market;
    $this->specialty = $survey_data->specialty;
    $this->brands = $survey_data->brands;
    $this->attributes = $survey_data->attributes;
    $this->patients = $survey_data->patients;
    $this->url_exit = $survey_data->url_exit;

    $this->setType('nps_plus');
    $this->setLanguages(array('en', 'fr', 'es', 'de', 'it'));
  }

  /**
   * Function to create think time analysis question.
   *
   */
  public function createTimerQuestion($data)
  {

    $sid = 0;
    $group_id = 0;
    $question_id = 0;
    $question_title = '';
    $language = '';
    $relevance = 0;

    $params = array('sid', 'group_id', 'question_id', 'question_title', 'relevance');
    foreach ($params as $param) {
      if (isset($data['sid'])) {
        ${$param} = $data[$param];
      }
    }

    $question = 'Time Thinking Analysis (hidden)';
    $help = '<script type="text/javascript" charset="utf-8">
$(document).ready(function(){
  $("#question{QID}").hide();
  $("#movenextbtn").click(function(){
   $("#answer{SID}X{GID}X{QID}").val(get_timethinking());
  });
});
</script>';

    $question_row =  array('qid' => $question_id,
      'parent_qid' => 0,
      'sid' => $sid,
      'gid' => $group_id,
      'type' => 'S',
      'title' => $question_title,
      'question' => $question,
      'help' => $help,
      'preg' => '',
      'other' => 'N',
      'mandatory' => 'N',
      'question_order' => 8,
      'scale_id' => 0,
      'same_default' => 0,
      'relevance' => $relevance,
      'language' => $language,
    );
    $this->addQuestion($question_row);
  }

  /**
   * Function to fill the arrays needed for the survey
   */
  public function createSurveyComponants()
  {

    $language = '';

    // ids
    // survey       100000
    // group        2000
    // questions    30000
    // subquestions 40000
    // condition    500

// ------------------------------------------------------------------------------

    $sid = '896139';

    // Survey settings
    $languages = $this->getLanguages();

    $main_language = 'en';
    $additional_languages = '';
    foreach ($languages as $language) {
      if ($language != $main_language) {
        $additional_languages .= $language . ' ';
      }
    }

    $survey_row = array('survey_id' => $sid,
      'adminemail' => 'simon.rainville@pslgroup.com',
      'faxto' => '5149999999',
      'bounce_email' => 'simon.rainville@pslgroup.com',
      'surveytemplate' => 'ls_google_mat',
      'language' => $main_language,
      'additional_languages' => $additional_languages,
    );
    $this->addSurvey($survey_row);

    //Language settings
    foreach ($languages as $language) {
      $file_name = 'PSLClipperBundle:limesurvey:NPSSurveyAssets/npsSurveyLanguageSettings.' . $language . '.xml.twig';
      $templating = $this->getContainer()->get('templating');
      $survey_language_settings_row = $templating->render($file_name,
        array('surveyls_survey_id' => $sid,
          'surveyls_url' => $this->url_exit,
        )
      );
      $this->addSurveysLanguagesetting($survey_language_settings_row);
    }

    //url parameters
    $survey_url_parameters_row = array('id' => '100',
      'sid' => $sid,
      'parameter' => 'lstoken',
      'targetqid' => '',
      'targetsqid' => '',
    );
    $this->addSurveyUrlParameter($survey_url_parameters_row);

    $survey_url_parameters_row = array('id' => '110',
      'sid' => $sid,
      'parameter' => 'd',
      'targetqid' => '',
      'targetsqid' => '',
    );
    $this->addSurveyUrlParameter($survey_url_parameters_row);

    $survey_url_parameters_row = array('id' => '120',
      'sid' => $sid,
      'parameter' => 'e',
      'targetqid' => '',
      'targetsqid' => '',
    );
    $this->addSurveyUrlParameter($survey_url_parameters_row);

    $survey_url_parameters_row = array('id' => '130',
      'sid' => $sid,
      'parameter' => 'c',
      'targetqid' => '',
      'targetsqid' => '',
    );
    $this->addSurveyUrlParameter($survey_url_parameters_row);

// ---------------------------------------------------------------------------------------------------------------------------
// pre group

// Screenout
// ---------------------------------------------------------------------------------------------------------------------------

    $group_order = 0;

    $pre_gid_0 = 1000;

    $pre_group_0_row = array('gid' => $pre_gid_0,
      'sid' => $sid,
      'group_name' => 'Screenout',
      'group_order' => $group_order,
      'language' => $language,
    );
    $this->addGroup($pre_group_0_row);

    $group_order++;

    // ------------------------------------------------------------------------------
    // pre group 0
    // question 0
    // Do you personally play an active prescribing role by writing prescriptions for patients with {PATIENT} ?
    // Please select one answer only.
    // ------------------------------------------------------------------------------

    $pre_qid_0_0 = 20000;
    // $question_0_0 = 'Thinking about the following drugs used to treat @patient_type, which of these statements applies to
    // each of them? Please select one response for each drug.';
    $pre_question_0_0 = 'pre-group-000.question-000';
    $pre_question_0_0_row = array('qid' => $pre_qid_0_0,
      'parent_qid' => 0,
      'sid' => $sid,
      'gid' => $pre_gid_0,
      'type' => 'Y',
      'title' => 'G000Q001',
      'question' => $pre_question_0_0,
      'help' => '',
      'preg' => '',
      'other' => 'N',
      'mandatory' => 'Y',
      'question_order' => 0,
      'scale_id' => 0,
      'same_default' => 0,
      'relevance' => 1,
      'language' => '',
      'tokens' => array('@patient_type' => $this->patients),
    );
    $this->addQuestion($pre_question_0_0_row);

    // ------------------------------------------------------------------------------
    // Quota 0
    // ------------------------------------------------------------------------------
    $quota_0_id = 300;
    $quota_0_name = 'Screenout';

    $quota = array('id' => $quota_0_id,
      'qid' => $pre_qid_0_0,
      'sid' => $sid,
      'name' => $quota_0_name,
      'qlimit' => 0,
      'action' => 1,
      'active' => 1,
      'autoload_url' => 0,
      'quota_id' => $quota_0_id,
      'code' => 'N',
    );

    // Integrate multiple language
    $quotals = array();
    $quotals_id = $quota_0_id;
    foreach ($languages as $key => $language) {
      $quotals_id += 10;
      $quotals[] = array(
        'quotals_id' => $quotals_id,
        'quotals_quota_id' => $quota_0_id,
        'quotals_language' => $language,
        'quotals_name' => $quota_0_name,
        'quotals_message' => $language . ' - Terminate message.',
        'quotals_url' => '',
        'quotals_urldescrip' => '',
      );
    }

    $quota['quotals'] = $quotals;
    $this->addQuota($quota);

// ---------------------------------------------------------------------------------------------------------------------------
// group 0

// Adoption
// ---------------------------------------------------------------------------------------------------------------------------

    $gid_0 = 2000;

    $group_0_row = array('gid' => $gid_0,
      'sid' => $sid,
      'group_name' => 'Adoption',
      'group_order' => $group_order,
      'language' => $language,
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

    // $question_0_0 = 'Thinking about the following drugs used to treat @patient_type, which of these statements applies to
    // each of them? Please select one response for each drug.';
    $question_0_0 = 'group-000.question-000';
    $question_0_0_help ="<script>
 $(document).ready(function(){
  $('table.questions-list').addClass('array_widget');
 });
</script>";

    $question_0_0_row = array('qid' => $qid_0_0,
      'parent_qid' => 0,
      'sid' => $sid,
      'gid' => $gid_0,
      'type' => 'F',
      'title' => 'G001Q001',
      'question' => $question_0_0,
      'help' => $question_0_0_help,
      'preg' => '',
      'other' => 'N',
      'mandatory' => 'Y',
      'question_order' => 0,
      'scale_id' => 0,
      'same_default' => 0,
      'relevance' => 1,
      'language' => '',
      'tokens' => array('@patient_type' => $this->patients),
    );
    $this->addQuestion($question_0_0_row);

    // ------------------------------------------------------------------------------
    // group 0
    // question 0
    // answers
    // ------------------------------------------------------------------------------

    $answers_0_0 = array(
      'group-000.question-000.answer-000', // 'I currently use this drug',
      'group-000.question-000.answer-001', // 'I have used this drug previously and would consider using it again',
      'group-000.question-000.answer-002', // 'I have used this drug previously but would no longer consider it',
      'group-000.question-000.answer-003', // 'I have never used this drug but would consider it',
      'group-000.question-000.answer-004', // 'I have never used this drug and would not consider it',
      'group-000.question-000.answer-005', // 'I am not aware of this drug',
    );

    foreach ($answers_0_0 as $key => $answer) {
      $index = $key + 1;
      $answer_row = array('qid' => $qid_0_0,
        'code' => 'A0' . $index,
        'answer' => $answer,
        'sortorder' => $index,
        'assessment_value' => 0,
        'language' => $language,
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
      $subquestion_row =   array('qid' => $subquestions_0_0 + $index,
        'parent_qid' => $qid_0_0,
        'sid' => $sid,
        'gid' => $gid_0,
        'type' => 'F',
        'title' => 'SQ00' . $index,
        'question' => $brand,
        'other' => 'N',
        'question_order' => $index,
        'scale_id' => 0,
        'language' => $language,
      );
      $this->addSubquestion($subquestion_row);
    }

    // ------------------------------------------------------------------------------
    // group 0
    // question 0
    // question_attributes
    // ------------------------------------------------------------------------------

    $question_attribute_0_0_row_0 =   array('qid' => $qid_0_0,
      'attribute' => 'printable_help',
      'value' => 1,
      'language' => $language,
    );
    $this->addQuestionAttribute($question_attribute_0_0_row_0);

    $question_attribute_0_0_row_1 = array('qid' => $qid_0_0,
      'attribute' => 'random_order',
      'value' => 1,
    );
    $this->addQuestionAttribute($question_attribute_0_0_row_1);

// ---------------------------------------------------------------------------------------------------------------------------

    // ------------------------------------------------------------------------------
    // group 0
    // question 1
    // Currently used (hidden)
    // hidden
    // ------------------------------------------------------------------------------

    $qid_0_1 = 30100;
    $question_0_1 = 'Currently used (hidden)';
    $question_0_1_help = '<script type="text/javascript" charset="utf-8">
$(document).ready(function(){
  $("#question{QID}").hide();
  $(document).on("click", "div .radio label", function (evt) {
    var input = $(this).find("input[type=\"radio\"]");
    var name = $(input).attr("name");
    var n = name.indexOf("SQ");
    var subQuestion = name.substring(n);
    var answer = $(input).attr("value");
    if (answer == "A01") {
      $("#java{SGQ}" + subQuestion).val("A1");
      $("#answer{SGQ}" + subQuestion + "-A1").attr("checked", "checked");
      $("#answer{SGQ}" + subQuestion + "-").attr("checked", false);
      //added for troubleshooting
      //$("#javatbd{SGQ}" + subQuestion + " .answer_cell_00A1").addClass("checked");
      //$("#javatbd{SGQ}" + subQuestion + " .noanswer-item").removeClass("checked");
    }
    else {
      $("#java{SGQ}" + subQuestion).val("");
      $("#answer{SGQ}" + subQuestion + "-A1").attr("checked", false);
      $("#answer{SGQ}" + subQuestion + "-").attr("checked", "checked");
      //added for troubleshooting
      //$("#javatbd{SGQ}" + subQuestion + " .answer_cell_00A1").removeClass("checked");
      //$("#javatbd{SGQ}" + subQuestion + " .noanswer-item").addClass("checked");
    }
  });
});
</script>';

      $question_0_1_row = array('qid' => $qid_0_1,
        'parent_qid' => 0,
        'sid' => $sid,
        'gid' => $gid_0,
        'type' => 'F',
        'title' => 'G001Q002',
        'question' => $question_0_1,
        'help' => $question_0_1_help,
        'preg' => '',
        'other' => 'N',
        'mandatory' => 'N',
        'question_order' => 4,
        'scale_id' => 0,
        'same_default' => 0,
        'relevance' => 1,
        'language' => $language,
      );
      $this->addQuestion($question_0_1_row);

    // ------------------------------------------------------------------------------
    // group 0
    // question 1
    // answers
    // ------------------------------------------------------------------------------

    $answer_0_1_row =  array('qid' => $qid_0_1,
      'code' => 'A1',
      'answer' => 'yes',
      'sortorder' => 1,
      'assessment_value' => 0,
      'language' => $language,
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
      $subquestion_row = array('qid' => $subquestions_0_1 + $index,
        'parent_qid' => $qid_0_1,
        'sid' => $sid,
        'gid' => $gid_0,
        'type' => 'F',
        'title' => 'SQ00' . $index,
        'question' => $brand,
        'other' => 'N',
        'question_order' => $index,
        'scale_id' => 0,
        'language' => $language,
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

    $qid_0_2 = 30200;
    $question_0_2 = 'Brand awareness (hidden)';
    $question_0_2_help = '<script type="text/javascript" charset="utf-8">
$(document).ready(function(){
  $("#question{QID}").hide();
  $(document).on("click", "div .radio label", function (evt) {
   var input = $(this).find("input[type=\"radio\"]");
   var name = $(input).attr("name");
   var n = name.indexOf("SQ");
   var subQuestion = name.substring(n);
   var answer = $(input).attr("value");
   if (answer != "A06") {
      $("#java{SGQ}" + subQuestion).val("A1");
      $("#answer{SGQ}" + subQuestion + "-A1").attr("checked", "checked");
      $("#answer{SGQ}" + subQuestion + "-").attr("checked", false);
      //added for troubleshooting
      //$("#javatbd{SGQ}" + subQuestion + " .answer_cell_00A1").addClass("checked");
      //$("#javatbd{SGQ}" + subQuestion + " .noanswer-item").removeClass("checked");
    }
    else {
      $("#java{SGQ}" + subQuestion).val("");
      $("#answer{SGQ}" + subQuestion + "-A1").attr("checked", false);
      $("#answer{SGQ}" + subQuestion + "-").attr("checked", "checked");
      //added for troubleshooting
      //$("#javatbd{SGQ}" + subQuestion + " .answer_cell_00A1").removeClass("checked");
      //$("#javatbd{SGQ}" + subQuestion + " .noanswer-item").addClass("checked");
    }
  });
});
</script>';

    $question_0_2_row = array('qid' => $qid_0_2,
      'parent_qid' => 0,
      'sid' => $sid,
      'gid' => $gid_0,
      'type' => 'F',
      'title' => 'G001Q003',
      'question' => $question_0_2,
      'help' => $question_0_2_help,
      'preg' => '',
      'other' => 'N',
      'mandatory' => 'N',
      'question_order' => 5,
      'scale_id' => 0,
      'same_default' => 0,
      'relevance' => 1,
      'language' => $language,
    );
    $this->addQuestion($question_0_2_row);

    // ------------------------------------------------------------------------------
    // group 0
    // question 1
    // answers
    // ------------------------------------------------------------------------------

    $answer_0_2_row = array('qid' => $qid_0_2,
      'code' => 'A1',
      'answer' => 'yes',
      'sortorder' => 1,
      'assessment_value' => 0,
      'language' => $language,
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
      $subquestion_row = array('qid' => $subquestions_0_2 + $index,
        'parent_qid' => $qid_0_2,
        'sid' => $sid,
        'gid' => $gid_0,
        'type' => 'F',
        'title' => 'SQ00' . $index,
        'question' => $brand,
        'other' => 'N',
        'question_order' => $index,
        'scale_id' => 0,
        'language' => $language,
      );
      $this->addSubquestion($subquestion_row);
    }

// ---------------------------------------------------------------------------------------------------------------------------

    // ------------------------------------------------------------------------------
    // group 0
    // question 3
    // Market (hidden)
    // hidden
    // ------------------------------------------------------------------------------

    $qid_0_3 = 30300;
    $question_0_3 = 'Market (hidden)';
    $question_0_3_help = '<script type="text/javascript" charset="utf-8">
$(document).ready(function(){
 $("#question{QID}").hide();
 $("#answer{SID}X{GID}X{QID}").val(' . $this->market . ');
});
</script>';

    $question_0_3_row =   array('qid' => $qid_0_3,
      'parent_qid' => 0,
      'sid' => $sid,
      'gid' => $gid_0,
      'type' => 'S',
      'title' => 'G001Q004',
      'question' => $question_0_3,
      'help' => $question_0_3_help,
      'preg' => '',
      'other' => 'N',
      'mandatory' => 'N',
      'question_order' => 6,
      'scale_id' => 0,
      'same_default' => 0,
      'relevance' => 1,
      'language' => $language,
    );
    $this->addQuestion($question_0_3_row);

// ---------------------------------------------------------------------------------------------------------------------------

    // ------------------------------------------------------------------------------
    // group 0
    // question 4
    // Specialty (hidden)
    // hidden
    // ------------------------------------------------------------------------------

    $qid_0_4 = 30400;

    $question_0_4 = 'Specialty (hidden)';
    $question_0_4_help = '<script type="text/javascript" charset="utf-8">
$(document).ready(function(){
 $("#question{QID}").hide();
 $("#answer{SID}X{GID}X{QID}").val(' . $this->specialty . ');
});
</script>';

    $question_0_4_row = array('qid' => $qid_0_4,
      'parent_qid' => 0,
      'sid' => $sid,
      'gid' => $gid_0,
      'type' => 'S',
      'title' => 'G001Q005',
      'question' => $question_0_4,
      'help' => $question_0_4_help,
      'preg' => '',
      'other' => 'N',
      'mandatory' => 'N',
      'question_order' => 7,
      'scale_id' => 0,
      'same_default' => 0,
      'relevance' => 1,
      'language' => $language,
    );
    $this->addQuestion($question_0_4_row);

// ---------------------------------------------------------------------------------------------------------------------------

    // ------------------------------------------------------------------------------
    // group 0
    // question 5
    // Time Thinking Analysis (hidden)
    // hidden
    // ------------------------------------------------------------------------------
    $qid_0_5 = 30500;

    $timer_data = array(
      'sid' => $sid,
      'group_id' => $gid_0,
      'question_id' => $qid_0_5,
      'question_title' => 'G001Q006',
      'relevance' => '1',
    );

    $this->createTimerQuestion($timer_data);


// ---------------------------------------------------------------------------------------------------------------------------
// group 1

// current prescribing
// ---------------------------------------------------------------------------------------------------------------------------

    $gid_1 = 2100;

    $group_1_row = array('gid' => $gid_1,
      'sid' => $sid,
      'group_name' => 'Current prescribing',
      'group_order' => $group_order,
      'language' => $language,
    );
    $this->addGroup($group_1_row);

    $group_order++;

    // ------------------------------------------------------------------------------
    // group 1
    // question 0
    // ------------------------------------------------------------------------------

    $qid_1_0 = 30600;
    // $question_1_0 = 'In a typical month what % of your patients for the treatment of @patient_type do you prescribe each of the
    // following drugs? We realise it is not possible to know this exactly, but we would sincerely appreciate your best
    // approximation. Please express your answer as a percentage. Your responses must add up to at least 100% and can be more if
    // drugs are co-prescribed.';
    $question_1_0 = 'group-001.question-000';
    $question_1_0_help = '<script type="text/javascript" charset="utf-8">
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

  // load default value
  var total_percentage_val = 0;
  $("input").not(".readonly, input[type=\"hidden\"]").each(function(){
    total_percentage_val += parseInt($(this).val(), 10) || 0;
  });
  $("#total-percentage").find("input").val(total_percentage_val);

  $("#movenextbtn").click(function(evt){
    if ($("#total-percentage").find("input").val() < 100) {
      alert("At least 100% for the total percentage");
      evt.preventDefault();
    }
  });
  $("input.text").attr("placeholder", "Enter a percentage");
});
</script>';

    $question_1_0_row = array('qid' => $qid_1_0,
      'parent_qid' => 0,
      'sid' => $sid,
      'gid' => $gid_1,
      'type' => ':',
      'title' => 'G002Q001',
      'question' => $question_1_0,
      'help' => $question_1_0_help,
      'preg' => '',
      'other' => 'N',
      'mandatory' => 'N',
      'question_order' => 1,
      'scale_id' => 0,
      'same_default' => 0,
      'relevance' => 1,
      'language' => $language,
      'tokens' => array('@patient_type' => $this->patients),
    );
    $this->addQuestion($question_1_0_row);

    // ------------------------------------------------------------------------------
    // group 1
    // question 0
    // subquestions
    // ------------------------------------------------------------------------------

    $subquestions_1_0 = 40300;

    // top row
    $subquestion_1_0_row = array('qid' => $subquestions_1_0,
      'parent_qid' => $qid_1_0,
      'sid' => $sid,
      'gid' => $gid_1,
      'type' => 'T',
      'title' => 'SQ001',
      'question' => '%',
      'other' => 'N',
      'question_order' => 0,
      'scale_id' => 1,
      'language' => $language,
    );
    $this->addSubquestion($subquestion_1_0_row);

    // loop through all brands
    foreach ($this->brands as $key => $brand) {
      $index = $key + 1;
      $subquestion_row = array('qid' => $subquestions_1_0 + $index,
        'parent_qid' => $qid_1_0,
        'sid' => $sid,
        'gid' => $gid_1,
        'type' => 'T',
        'title' => 'SQ00' . $index,
        'question' => $brand,
        'other' => 'N',
        'question_order' => $index,
        'scale_id' => 0,
        'language' => $language,
      );
      $this->addSubquestion($subquestion_row);
    }

    // ------------------------------------------------------------------------------
    // group 1
    // question 0
    // attributes
    // ------------------------------------------------------------------------------

    $attributes_1_0_row_0 = array('qid' => $qid_1_0,
      'attribute' => 'array_filter',
      'value' => 'G001Q002',
    );
    $this->addQuestionAttribute($attributes_1_0_row_0);

    $attributes_1_0_row_1 = array('qid' => $qid_1_0,
      'attribute' => 'input_boxes',
      'value' => 1,
    );
    $this->addQuestionAttribute($attributes_1_0_row_1);

    $attributes_1_0_row_2 = array('qid' => $qid_1_0,
      'attribute' => 'printable_help',
      'value' => 1,
      'language' => $language,
    );
    $this->addQuestionAttribute($attributes_1_0_row_2);

    $attributes_1_0_row_3 = array('qid' => $qid_1_0,
      'attribute' => 'random_order',
      'value' => 1,
    );
    $this->addQuestionAttribute($attributes_1_0_row_3);

// ---------------------------------------------------------------------------------------------------------------------------

    // ------------------------------------------------------------------------------
    // group 1
    // question 2
    // Time Thinking Analysis (hidden)
    // hidden
    // ------------------------------------------------------------------------------

    $qid_1_2 = 30700;
    $timer_data = array(
      'sid' => $sid,
      'group_id' => $gid_1,
      'question_id' => $qid_1_2,
      'question_title' => 'G002Q002',
      'relevance' => '1',
    );
    $this->createTimerQuestion($timer_data);

// ---------------------------------------------------------------------------------------------------------------------------
// group 2

// NPS
// ---------------------------------------------------------------------------------------------------------------------------


    $gid_2 = 2200;

    $group_1_row = array('gid' => $gid_2,
      'sid' => $sid,
      'group_name' => 'NPS',
      'group_order' => $group_order,
      'language' => $language,
    );
    $this->addGroup($group_1_row);

    $group_order++;

    // ------------------------------------------------------------------------------
    // group 2
    // question 0
    // ------------------------------------------------------------------------------

    $qid_2_0 = 30800;

    // $question_2_0 = 'If a colleague would ask you for your recommendation, how likely would you be to recommend each of the
    // following drugs for the treatment of @patient_type. Please select a response for each drug.<br/>0= not at all
    // likely, 10 = extremely likely';
    $question_2_0 = 'group-002.question-000';
    $question_2_0_help = "<script>
$(document).ready(function(){
 $('table.question').addClass('num-rating');
});
</script>";
    $question_2_0_row = array('qid' => $qid_2_0,
      'parent_qid' => 0,
      'sid' => $sid,
      'gid' => $gid_2,
      'type' => 'B',
      'title' => 'G003Q001',
      'question' => $question_2_0,
      'help' => $question_2_0_help,
      'preg' => '',
      'other' => 'N',
      'mandatory' => 'Y',
      'question_order' => 1,
      'scale_id' => 0,
      'same_default' => 0,
      'relevance' => 1,
      'language' => $language,
      'tokens' => array('@patient_type' => $this->patients),
    );
    $this->addQuestion($question_2_0_row);

    // ------------------------------------------------------------------------------
    // group 2
    // question 0
    // subquestions
    // ------------------------------------------------------------------------------

    $subquestions_2_0 = 40400;

    // loop through all brands
    foreach ($this->brands as $key => $brand) {
      $index = $key + 1;
      $subquestion_row = array('qid' => $subquestions_2_0 + $index,
        'parent_qid' => $qid_2_0,
        'sid' => $sid,
        'gid' => $gid_2,
        'type' => 'T',
        'title' => 'SQ00' . $index,
        'question' => $brand,
        'other' => 'N',
        'question_order' => $index,
        'scale_id' => 0,
        'language' => $language
      );
      $this->addSubquestion($subquestion_row);
    }

    // ------------------------------------------------------------------------------
    // group 2
    // question 0
    // attributes
    // ------------------------------------------------------------------------------

    $attributes_2_0_row = array('qid' => $qid_2_0,
      'attribute' => 'array_filter',
      'value' => 'G001Q003',
    );
    $this->addQuestionAttribute($attributes_2_0_row);

// ---------------------------------------------------------------------------------------------------------------------------

    // ------------------------------------------------------------------------------
    // group 2
    // question 1
    // Time Thinking Analysis (hidden)
    // hidden
    // ------------------------------------------------------------------------------

    $qid_2_1 = 30900;
    $timer_data = array(
      'sid' => $sid,
      'group_id' => $gid_2,
      'question_id' => $qid_2_1,
      'question_title' => 'G003Q002',
      'relevance' => '1',
    );
    $this->createTimerQuestion($timer_data);

// ---------------------------------------------------------------------------------------------------------------------------
// group 3

// Brand DNA
// ---------------------------------------------------------------------------------------------------------------------------

    $gid_3 = 2300;

    // loop through all brands
    // to create group, questions, answers, and all
    foreach ($this->brands as $key => $brand) {

      // ------------------------------------------------------------------------------
      // Group
      // ------------------------------------------------------------------------------

      $index = $key + 1;
      $group_id = $gid_3 + $index;

      $group_3_row = array('gid' => $group_id,
        'sid' => $sid,
        'group_name' => 'Brand DNA - ' . $brand,
        'group_order' => $group_order,
        'language' => $language,
      );
      $this->addGroup($group_3_row);

      $group_order++;

      // ------------------------------------------------------------------------------
      // Question
      // Thinking about the treatment of your {PATIENT}. How would you summarise in a short phrase of no more than
      // 10 words what {BRAND} means to you? Please use no more than 10 words.
      // ------------------------------------------------------------------------------

      $qid_3_0 = 31000;
      // $question_3_0 = 'Thinking about the treatment of your @patient_type. How would you summarise in a short phrase of no
      // more than 10 words what @brand means to you? Please use no more than 10 words.';
      $question_3_0 = 'group-003.question-000';
      $question_3_0_help = '<script type="text/javascript" charset="utf-8">
$(document).ready(function(){
 $("textarea").attr("placeholder", "Enter comments")
});
</script>';

      $title = 'G00' .  $group_order  . 'Q001';
      $sgq = $sid . 'X' . $gid_0 . 'X' . $qid_0_2 . 'SQ00' . $index;

      $question_3_0_row = array('qid' => $qid_3_0 + $index,
        'parent_qid' => 0,
        'sid' => $sid,
        'gid' => $gid_3 + $index,
        'type' => 'T',
        'title' => $title,
        'question' => $question_3_0,
        'help' => $question_3_0_help,
        'preg' => '',
        'other' => 'N',
        'mandatory' => 'Y',
        'question_order' => 1,
        'scale_id' => 0,
        'same_default' => 0,
        'relevance' => '((' . $sgq . '.NAOK == "A1"))',
        'language' => $language,
        'tokens' => array('@patient_type' => $this->patients, '@brand' => $brand),
      );
      $this->addQuestion($question_3_0_row);

      // ------------------------------------------------------------------------------
      // condition
      // ------------------------------------------------------------------------------

      $cid_3_0 = 500;

      $condition_3_row = array('cid' => $cid_3_0 + $index,
        'qid' => $qid_3_0 + $index,
        'cqid' => $qid_0_2,
        'cfieldname' => $sgq,
        'method' => '==',
        'value' => 'A1',
        'scenario' => 1,
      );
      $this->addCondition($condition_3_row);

      // ------------------------------------------------------------------------------
      // Time Thinking Analysis (hidden)
      // hidden
      // ------------------------------------------------------------------------------

      $qid_3_1 = 31100;

      $timer_data = array(
        'sid' => $sid,
        'group_id' => $gid_3 + $index,
        'question_id' => $qid_3_1 + $index,
        'question_title' => 'G00' . $group_order . 'Q002',
        'relevance' => '((' . $sgq . '.NAOK == "A1"))',
      );
      $this->createTimerQuestion($timer_data);

    }

// ---------------------------------------------------------------------------------------------------------------------------
// group 4

// Brand Association
// ---------------------------------------------------------------------------------------------------------------------------

    $gid_4 = 2400;

    // ------------------------------------------------------------------------------
    // Group
    // ------------------------------------------------------------------------------

    $group_4_row = array('gid' => $gid_4,
      'sid' => $sid,
      'group_name' => 'Brand Association',
      'group_order' => $group_order,
      'language' => $language,
    );
    $this->addGroup($group_4_row);

    $group_order++;


    // ------------------------------------------------------------------------------
    // Question
    // ------------------------------------------------------------------------------
    $qid_4_0 = 31200;
    // $question_4_0 = 'Below are a series of statements. Please select "Yes" if you associate that statement
    // with @brand or "No" if you do not.';
    $question_4_0 = 'group-004.question-000';
    $question_4_0_help ="<script>
 $(document).ready(function(){
  $('table.questions-list').addClass('array_widget');
 });
</script>";
    $title = 'G00' . $group_order . 'Q001';
    $sgq = $sid . 'X' . $gid_0 . 'X' . $qid_0_2 . 'SQ001';

    $question_4_0_row = array('qid' => $qid_4_0,
      'parent_qid' => 0,
      'sid' => $sid,
      'gid' => $gid_4,
      'type' => 'F',
      'title' => $title,
      'question' => $question_4_0,
      'help' => $question_4_0_help,
      'preg' => '',
      'other' => 'N',
      'mandatory' => 'Y',
      'question_order' => 1,
      'scale_id' => 0,
      'same_default' => 0,
      'relevance' => 1,
      'language' => $language,
      'tokens' => array('@brand' => $brand),
    );
    $this->addQuestion($question_4_0_row);

    // ------------------------------------------------------------------------------
    // Time Thinking Analysis (hidden)
    // hidden
    // ------------------------------------------------------------------------------
    $qid_4_1 = 31300;
    $timer_data = array(
      'sid' => $sid,
      'group_id' => $gid_4,
      'question_id' => $qid_4_1,
      'question_title' => 'G00' . $group_order . 'Q002',
      'relevance' => '1',
    );
    $this->createTimerQuestion($timer_data);

    // ------------------------------------------------------------------------------
    // Subquestion
    // Attributes
    // ------------------------------------------------------------------------------

    $subquestions_4_0 = 40500;

    // loop through all attributes
    foreach ($this->attributes as $key => $attribute) {
      $sub_index = $key + 1;
      $subquestion_row = array('qid' => $subquestions_4_0 * 10 + $sub_index,
        'parent_qid' => $qid_4_0,
        'sid' => $sid,
        'gid' => $gid_4,
        'type' => 'T',
        'title' => 'SQ00' . $sub_index,
        'question' => $attribute,
        'other' => 'N',
        'question_order' => $sub_index,
        'scale_id' => 0,
        'language' => $language,
      );
      $this->addSubquestion($subquestion_row);
    }

    // ------------------------------------------------------------------------------
    // answers
    // ------------------------------------------------------------------------------

    // loop through all brands
    foreach ($this->brands as $key => $brand) {
      $index = $key + 1;
      $answer_4_0_row = array('qid' => $qid_4_0,
        'code' => 'A0' . $index,
        'answer' => $brand,
        'sortorder' => $index,
        'assessment_value' => 0,
        'language' => $language,
      );
      $this->addAnswer($answer_4_0_row);
    }

    // Non of these answer
    $answer_4_0_row = array('qid' => $qid_4_0,
      'code' => 'A0' . ($index + 1),
      'answer' => 'None of these',
      'sortorder' => $index + 1,
      'assessment_value' => 0,
      'language' => $language,
    );
    $this->addAnswer($answer_4_0_row);

    // ------------------------------------------------------------------------------
    // Question attributes
    // ------------------------------------------------------------------------------

    $question_attribute_4_0_row = array('qid' => $qid_4_0,
      'attribute' => 'random_order',
      'value' => 1,
    );
    $this->addQuestionAttribute($question_attribute_4_0_row);

    return $this;
  }
}
