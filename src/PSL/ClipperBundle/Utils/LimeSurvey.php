<?php

namespace PSL\ClipperBundle\Utils;

use org\jsonrpcphp\JsonRPCClient;
use \Exception as Exception;

/**
 * helper class to interact with LimeSurvey
 */
  
class LimeSurvey
{
  private $response;
  private $session_key;
  private $release_session_key;
  private $client;
  private $ls_baseurl;
  private $ls_user;
  private $ls_password;
  private $param_arr;
  
  /**
   * Configure the API client with the required credentials.
   *
   * Requires a settings array to be passed in with the following keys:
   *
   * - ls_baseurl
   * - ls_user
   * - ls_password
   *
   * @param array $settings
   * @throws \Exception
   */
  public function configure(array $settings)
  {
    if (!isset($settings['ls_baseurl'])) {
        throw new Exception("'ls_baseurl' must be provided");
    }

    if (!isset($settings['ls_user'])) {
        throw new Exception("'ls_user' must be provided");
    }

    if (!isset($settings['ls_password'])) {
        throw new Exception("'ls_password' must be provided");
    }

    $this->ls_baseurl = $settings['ls_baseurl'];
    $this->ls_user = $settings['ls_user'];
    $this->ls_password = $settings['ls_password'];
    
    // instanciate a new JsonRPCClient client
    $this->client = new JsonRPCClient($this->ls_baseurl);
    
    // request session key
    $this->session_key = $this->client->get_session_key($this->ls_user, $this->ls_password);
  }
  
  /**
   * @return response from client
   */
  private function call($callback, $param_arr) 
  {
    // call $callback and pass $param_arr to it
    $this->response = call_user_func_array(array($this->client, $callback), $param_arr);
    $this->param_arr = $param_arr;
    
    // release session key
    // $this->release_session_key = $this->client->release_session_key($this->session_key);
    
    return $this->response;
  }
  
  /**
   * @return $this as strings
   */
   public function __toString()
   {
     return print_r($this, 1);
   }
  
  /**
  * RPC Routine to import a survey - imports lss,csv,xls or survey zip archive.
  */
  public function import_survey($args = array()) 
  {
    /**
     * @param string $sSessionKey Auth Credentials
     * @param string $sImportData String containing the BASE 64 encoded data of a lss,csv,xls or survey zip archive
     * @param string $sImportDataType lss,csv,xls or zip
     * @param string $sNewSurveyName The optional new name of the survey
     * @param integer $DestSurveyID This is the new ID of the survey - if already used a random one will be taken instead
     * @return array|integer iSurveyID - ID of the new survey
     */
    $param_arr = array_merge(array(
      'sSessionKey' => $this->session_key,
      'sImportData' => null, 
      'sImportDataType' => 'lss', 
      'sNewSurveyName' => null, 
      'DestSurveyID' => null,
    ), $args);
    
    return $this->call(__FUNCTION__, $param_arr);
  }
  
  /**
  * RPC Routine that launches a newly created survey.
  */
  public function activate_survey($args = array()) 
  {
    /**
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID The id of the survey to be activated
     * @return array The result of the activation
     */
    $param_arr = array_merge(array(
      'sSessionKey' => $this->session_key,
      'iSurveyID' => null, 
    ), $args);
    
    return $this->call(__FUNCTION__, $param_arr);
  }
  
  /**
  * RPC routine to to initialise the survey's collection of tokens where new participant tokens may be later added
  */
  public function activate_tokens($args = array()) 
  {
    /**
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param integer $iSurveyID ID of the survey where a token table will be created for
     * @param array $aAttributeFields An array of integer describing any additional attribute fields
     * @return array Status=>OK when successfull, otherwise the error description
     */
    $param_arr = array_merge(array(
      'sSessionKey' => $this->session_key,
      'iSurveyID' => null, 
      'additional_attributes' => array(), 
    ), $args);
    
    return $this->call(__FUNCTION__, $param_arr);
  }
  
  
  /**
  * RPC Routine to add participants to the tokens collection of the survey.
  * Returns the inserted data including additional new information like the Token entry ID and the token string.
  */
  // SAMPLE RESPONSE, array of arrays
  // 0 => 
  // array (size=17)
    // 'sent' => string 'N' (length=1)
    // 'remindersent' => string 'N' (length=1)
    // 'remindercount' => int 0
    // 'completed' => string 'N' (length=1)
    // 'usesleft' => int 1
    // 'email' => string 'fq1@pslgroup.com' (length=16)
    // 'lastname' => string 'fq1' (length=3)
    // 'firstname' => string 'fq1' (length=3)
    // 'token' => string 'xfeyad3sr65qmrf' (length=15)
    // 'tid' => string '5' (length=1)
    // 'participant_id' => null
    // 'emailstatus' => null
    // 'language' => null
    // 'blacklisted' => null
    // 'validfrom' => null
    // 'validuntil' => null
    // 'mpid' => null
  public function add_participants($args = array())
  {
    /**
    * @param string $sSessionKey Auth credentials
    * @param int $iSurveyID Id of the Survey
    * @param struct $aParticipantData 
    *   Data of the participants to be added, 
    *   2-dimensional array/structure containing your participants data, Example: 
    *   [ {"email":"me@example.com","lastname":"Bond","firstname":"James"} ]
    * @param bool createTokenKey Optional - Defaults to true and determins if the access token automatically created
    * @return array The values added
    */
    $param_arr = array_merge(array(
      'sSessionKey' => $this->session_key,
      'iSurveyID' => null, 
      'participantData' => array(), 
      'createTokenKey' => TRUE, 
    ), $args);
    
    return $this->call(__FUNCTION__, $param_arr);
  }
  
  /**
  * RPC Routine to export responses.
  * Returns the requested file as base64 encoded string
  * */
  public function export_responses($args = array()) 
  {
    /**
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID Id of the Survey
     * @param string $sDocumentType pdf,csv,xls,doc,json
     * @param string $sLanguageCode The language to be used
     * @param string $sCompletionStatus Optional 'complete','incomplete' or 'all' - defaults to 'all'
     * @param string $sHeadingType 'code','full' or 'abbreviated' Optional defaults to 'code'
     * @param string $sResponseType 'short' or 'long' Optional defaults to 'short'
     * @param integer $iFromResponseID Optional
     * @param integer $iToResponseID Optional
     * @param array $aFields Optional Selected fields
     * @return array|string On success: Requested file as base 64-encoded string. On failure array with error information
     */
    $param_arr = array_merge(array(
      'sSessionKey' => $this->session_key,
      'iSurveyID' => null, 
      'sDocumentType' => 'csv', 
      'sLanguageCode' => 'en', 
      'sCompletionStatus' => 'all',
      'sHeadingType' => 'full',
      'sResponseType' => 'long',
      'iFromResponseID' => null,
      'iToResponseID' => null,
      'aFields' => null,
    ), $args);
    
    return $this->call(__FUNCTION__, $param_arr);
  }

  /**
   * RPC Routine to return settings of a token/participant of a survey
   * 
   * The following properties of tokens can be read or set:
   * aTokenProperties
   *  tid             int     Token ID; read-only property
      completed       string  N or Y
      participant_id    
      language        string  
      usesleft    
      firstname       String  Participant's first name
      lastname        String  Participant's last name
      email           String  Participant's e-mail address
      blacklisted   
      validfrom   
      sent    
      validuntil    
      remindersent    
      mpid    
      emailstatus   
      remindercount
   */
   public function get_participant_properties($args = array())
   {
     /**
      * @access public
      * @param string $sSessionKey Auth credentials
      * @param int $iSurveyID Id of the Survey to get token properties
      * @param int $iTokenID Id of the participant to check
      * @param array $aTokenProperties The properties to get
      * @return array The requested values
      */
      $param_arr = array_merge(array(
        'sSessionKey' => $this->session_key,
        'iSurveyID' => null, 
        'iTokenID' => null, 
        'aTokenProperties' => array(), // The properties to get
      ), $args);
    
      return $this->call(__FUNCTION__, $param_arr);
   }

  /**
   * RPC Routine to set survey properties.
   * 
   * Available properties
   * 
      Allways:
        sid
        language
        additional_languages
        active
      When survey active:
        anonymized
        datestamp
        savetimings
        ipaddr
        refurl
   */
  public function set_survey_properties($args = array()) 
  {
    /**
    * @access public
    * @param string $sSessionKey Auth credentials
    * @param integer $iSurveyID - ID of the survey
    * @param array|struct $aSurveyData - An array with the particular fieldnames as keys and their values to set on that particular survey
    * @return array Of succeeded and failed nodifications according to internal validation.
    */
    $param_arr = array_merge(array(
      'sSessionKey' => $this->session_key,
      'iSurveyID' => null, 
      'aSurveyData' => array(), 
    ), $args);
    
    return $this->call(__FUNCTION__, $param_arr);
  }
  
  /**
   * RPC Routine to get survey properties.
   * active autonumber_start  emailnotificationto nokeyboard  showwelcome
additional_languages  autoredirect  emailresponseto owner_id  showxquestions
admin bounce_email  expires printanswers  sid
adminemail  bounceaccountencryption faxto publicgraphs  startdate
alloweditaftercompletion  bounceaccounthost format  publicstatistics  template
allowjumps  bounceaccountpass googleanalyticsapikey refurl  tokenanswerspersistence
allowprev bounceaccounttype googleanalyticsstyle  savetimings tokenlength
allowregister bounceaccountuser htmlemail sendconfirmation  usecaptcha
allowsave bounceprocessing  ipaddr  showgroupinfo usecookie
anonymized  bouncetime  language  shownoanswer  usetokens
assessments datecreated listpublic  showprogress  
attributedescriptions datestamp navigationdelay showqnumcode
   */
  public function get_survey_properties($args = array()) 
  {
    /**
    * @access public
    * @param string $sSessionKey Auth credentials
    * @param int $iSurveyID The id of the Survey to be checked
    * @param array $aSurveySettings The properties to get
    * @return array
    */
    $param_arr = array_merge(array(
      'sSessionKey' => $this->session_key,
      'iSurveyID' => null, 
      'aSurveySettings' => array(), 
    ), $args);
    
    return $this->call(__FUNCTION__, $param_arr);
  }
  
  /**
  * RPC Routine to return the ids and info of token/participants of a survey.
  * if $bUnused is true, user will get the list of not completed tokens (token_return functionality).
  * Parameters iStart and ilimit are used to limit the number of results of this call.
  */
  public function list_participants($args = array()) 
  {
    /**
    * @access public
    * @param string $sSessionKey Auth credentials
    * @param int $iSurveyID Id of the survey to list participants
    * @param int $iStart Start id of the token list
    * @param int $iLimit Number of participants to return
    * @param bool $bUnused If you want unused tokensm, set true
    * @return array The list of tokens
    */
    $param_arr = array_merge(array(
      'sSessionKey' => $this->session_key,
      'iSurveyID' => null, 
      'iStart' => null, 
      'iLimit' => null, 
      'bUnused' => true, 
    ), $args);
    
    return $this->call(__FUNCTION__, $param_arr);
  }

}