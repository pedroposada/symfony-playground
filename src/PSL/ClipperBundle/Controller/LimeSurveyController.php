<?php

namespace PSL\ClipperBundle\Controller;

use org\jsonrpcphp\JsonRPCClient;

/**
 * helper class to interact with LimeSurvey
 */
  
class LimeSurveyController extends Controller
{
  protected $response;
  protected $session_key;
  protected $release_session_key;
  protected $client;
  protected $callback = '';
  protected $param_arr = array();
  protected $settings;
  
  public function __construct($config = array()) 
  {
    // settings
    $this->settings = $config;

    // instanciate a new JsonRPCClient client
    $ls_baseurl = $this->getSetting('ls_baseurl');
    $this->client = new JsonRPCClient($ls_baseurl);
    
    // request session key
    $ls_user = $this->getSetting('ls_user');
    $ls_password = $this->getSetting('ls_password');
    $this->session_key = $this->client->get_session_key($ls_user, $ls_password);
  }
  
  /**
   * @return response from client
   */
  public function execute() 
  {
    $callback = $this->callback;
    $param_arr = $this->param_arr;
    
    // client callback and pass param_arr to it
    if (!empty($callback) && !empty($param_arr)) {
      $this->response = call_user_func_array(array($this->client, $callback), $param_arr);
    }
    
    // release session key
    $this->release_session_key = $this->client->release_session_key($this->session_key);
    
    // call logging function
    if (function_exists($this->getSetting('logging_function'))) {
      $logging_function = $this->getSetting('logging_function');
      $logging_function(array('callback' => $callback, 'param_arr' => $param_arr, 'object' => $this));
    }
    
    return $this->response;
  }
  
  /**
   * settings wrapper
   */
  protected function getSetting($name = NULL, $default_value = NULL) 
  {
    return isset($this->settings[$name]) ? $this->settings[$name] : $default_value;
  }
  
  /**
  * RPC Routine to import a survey - imports lss,csv,xls or survey zip archive.
  */
  public function import_survey($args = array()) 
  {
    $this->callback = __FUNCTION__;
    /**
     * @param string $sSessionKey Auth Credentials
     * @param string $sImportData String containing the BASE 64 encoded data of a lss,csv,xls or survey zip archive
     * @param string $sImportDataType lss,csv,xls or zip
     * @param string $sNewSurveyName The optional new name of the survey
     * @param integer $DestSurveyID This is the new ID of the survey - if already used a random one will be taken instead
     * @return array|integer iSurveyID - ID of the new survey
     */
    $this->param_arr = array_merge(array(
      'sSessionKey' => $this->session_key,
      'sImportData' => null, 
      'sImportDataType' => 'lss', 
      'sNewSurveyName' => null, 
      'DestSurveyID' => null,
    ), $args);
    
    return $this;
  }
  
  /**
  * RPC Routine that launches a newly created survey.
  */
  public function activate_survey($args = array()) 
  {
    $this->callback = __FUNCTION__;
    /**
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID The id of the survey to be activated
     * @return array The result of the activation
     */
    $this->param_arr = array_merge(array(
      'sSessionKey' => $this->session_key,
      'iSurveyID' => null, 
    ), $args);
    
    return $this;
  }
  
  
  /**
  * RPC Routine to export responses.
  * Returns the requested file as base64 encoded string
  *
  * */
  public function export_responses($args = array()) 
  {
    $this->callback = __FUNCTION__;
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
    $this->param_arr = array_merge(array(
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
    
    return $this;
  }
  
    
}