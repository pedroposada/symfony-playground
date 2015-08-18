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
    
    $fwsso_baseurl = $settings['fwsso_baseurl'];
  }
  
  /**
   * @return response from client
   */
  private function getCall($action, $param_arr) 
  {
    
    $endpoint = $fwsso_baseurl . $action;
    
    $browser = new Browser();
    $response = $browser->get($endpoint);
    
    return $response;
  }
  
  /**
   * @return response from client
   */
  private function postCall($action, $param_arr) 
  {
    
    $endpoint = $fwsso_baseurl . $action;
    
    $headers = array('Content-Type'=> 'application/json');
    
    // @TODO: use serializer class
    $content = json_encode($param_arr); 
    
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
    $action = '/users/edit/' . $args['uid'];
    
    return $this->postCall($action, $param_arr);
  }
  
  /**
   * Login user
   */
  public function loginUser($param_arr = array())
  {
    $action = '/users/login';
    
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
    $action = '/users/' + $param_arr['uid'];
    
    return $this->getCall($action, $param_arr);
  }
  
  /**
   * 
   */
  public function getHash($param_arr = array())
  {
    // @TODO: make this work for realz
    // return hash('sha256', $param_arr['salt'] . $param_arr['raw']);
    $action = '/users/hash';
    
    return $this->postCall($action, $param_arr);
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
