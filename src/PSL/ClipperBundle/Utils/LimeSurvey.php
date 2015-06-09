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
    
    return $this->call('import_survey', $param_arr);
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
    
    return $this->call('activate_survey', $param_arr);
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
    
    return $this->call('export_responses', $param_arr);
  }
    
}