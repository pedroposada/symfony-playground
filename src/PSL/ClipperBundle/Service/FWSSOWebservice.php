<?php

namespace PSL\ClipperBundle\Service;

use Buzz\Browser;

use \Exception as Exception;

/**
 * helper class to interact with LimeSurvey
 */
class FWSSOWebservice
{
  
  private $fwsso_baseurl;
  private $fwsso_app_token;

  /**
   * Configure the API client with the required credentials.
   *
   * Requires a settings array to be passed in with the following keys:
   *
   * - fwsso_baseurl
   *
   * @param array $settings
   * @throws \Exception
   */
  public function configure(array $settings)
  {
    // Validate URL
    if (!isset($settings['fwsso_baseurl'])) {
        throw new Exception("'fwsso_baseurl' must be provided");
    } 
    $this->fwsso_baseurl = $settings['fwsso_baseurl'];

    // Validate URL
    if (!isset($settings['fwsso_app_token'])) {
        throw new Exception("'fwsso_app_token' must be provided");
    } 
    $this->fwsso_app_token = $settings['fwsso_app_token'];
  }
  
  /**
   * @return response from client
   */
  private function getCall($action, $param_arr) 
  {
    
    $endpoint = $this->fwsso_baseurl . $action;

    $headers = array('Content-Type'=> 'application/x-www-form-urlencoded');
    $headers = array('X-FWSSO-Client' => $this->fwsso_app_token);
    
    $browser = new Browser();
    $response = $browser->get($endpoint, $headers);
    
    return $response;
  }
  
  /**
   * @return response from client
   */
  private function postCall($action, $param_arr) 
  {
    $endpoint = $this->fwsso_baseurl . $action;
    
    $headers = array('Content-Type'=> 'application/x-www-form-urlencoded');
    $headers = array('X-FWSSO-Client' => $this->fwsso_app_token);
    $content = http_build_query($param_arr);
    
    // @TODO: use serializer class
    //$content = json_encode($param_arr);
    
    $browser = new Browser();

    $response = $browser->post($endpoint, $headers, $content);
    return $response;
  }
  
  /**
   * ----------------------------------------------------------------------------------------
   * API
   * ----------------------------------------------------------------------------------------
   */
  
  /**
   * Create a new user
   */
  public function createUser($param_arr = array())
  {
    $action = '/users';
    
    return $this->postCall($action, $param_arr);
  }
  
  /**
   * Edit a current user
   */
  public function editUser($param_arr = array())
  {
    $action = '/users/edit/' . $param_arr['uid'];
    $param_arr['signature'] = time();
    
    return $this->postCall($action, $param_arr);
  }
  
  /**
   * Login user
   */
  public function loginUser($param_arr = array())
  {
    // We use getUser endpoint to retrieve the user.
    $getUser = $this->getUser(array(
      'uid' => $param_arr['username']
    ));

    return $getUser;
  }

  /**
   * QuickLogin user
   */
  public function quickLoginUser($param_arr = array())
  {
    $action = '/users/ql/';

    return $this->postCall($action, array(
      'qlhash' => $param_arr['qlhash']
    ));
  }

  /**
   * Change password
   */
  public function changePassword($param_arr = array())
  {
    $action = '/users/changepassword';

    return $this->postCall($action, $param_arr);
  }
  
  /**
   * Forgot password
   */
  public function forgotPassword($param_arr = array())
  {
    $action = '/users/forgotpassword';
    
    return $this->postCall($action, $param_arr);
  }
  
  /**
   * 
   */
  public function getUser($param_arr = array())
  {
    $action = '/users/' . $param_arr['uid'] . '/' . time();
    
    return $this->getCall($action, $param_arr);
  }
  
  /**
   * 
   */
  public function getHash($param_arr = array())
  {
    $action = '/users/hash/';
    
    return $this->postCall($action, array(
      'string' => $param_arr['raw'],
      'salt' => $param_arr['salt']
    ));
  }

  /**
   * ----------------------------------------------------------------------------------------
   * Helper functions
   * ----------------------------------------------------------------------------------------
   */
  
  /**
   * @return $this as strings
   */
   public function __toString()
   {
     return print_r($this, 1);
   }
  
}
