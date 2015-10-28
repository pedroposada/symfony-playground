<?php
/**
 * Main Clipper User Controller
 */

namespace PSL\ClipperBundle\Controller;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\VarDumper;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\View\View;

use PSL\ClipperBundle\Utils\FWSSOWebservices as FWSSOWebservices;
use PSL\ClipperBundle\Security\User\FWSSOQuickLoginUser as FWSSOQuickLoginUser;

use \stdClass as stdClass;
use \Exception as Exception;
use \DateTime as DateTime;
use \DateTimeZone as DateTimeZone;

/**
 * Rest Controller for Clipper
 */
class ClipperUserController extends FOSRestController
{
  
  public function __construct()
  {
    
  }
  
  private function fwsso_ws()
  {
    $settings['fwsso_baseurl'] = $this->container->getParameter('fwsso_api.url');
    $settings['fwsso_app_token'] = $this->container->getParameter('fwsso_api.app_token');
    $fwsso_ws = $this->container->get('fw_sso_webservice');
    $fwsso_ws->configure($settings);
    return $fwsso_ws;
  }
  
  /**
   * ----------------------------------------------------------------------------------------
   * API
   * ----------------------------------------------------------------------------------------
   */
  
  /**
   * Create a FWSSO user.
   *
   * @ApiDoc(
   *   resource=true,
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     204 = "No Content for the parameters passed"
   *   }
   * )
   * 
   * /api/newuser
   * This API is accessible anonymously
   * 
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function postNewuserAction(ParamFetcher $paramFetcher) 
  {
    // Object to return to remote form
    $returnObject = array();
    $responseStatus = 200;

    // POST params
    $params = $this->getUserFields();
    $this->prepareParamFetcher($paramFetcher, $params);
    
    try {

      // get $_POST values
      $user = array();
      $data = $paramFetcher->all();
      // Map POST data to FWSSO API names
      foreach ($data as $param => $value) {
        if (isset($params[$param]['fwsso_name'])) {
          $user[$params[$param]['fwsso_name']] = $value;
        }
      }
      // FW SSO API - create user
      $fwsso_ws = $this->fwsso_ws();
      $response = $fwsso_ws->createUser($user);

      if ($response->isOk()) {
        $content = @json_decode($response->getContent(), TRUE);
        if (json_last_error() != JSON_ERROR_NONE) {
          $this->logger = $this->container->get('monolog.logger.clipper');
          // Return operation specific error
          $returnObject['user'] = FALSE;
          $returnObject['error_message'] = 'An error has occurred. Please try again.';
          $this->logger->debug('Create account - JSON decode error: ' . json_last_error());
          $responseStatus = 500;
        }
        $returnObject['user'] = $content;
      } 
      else {
        throw new Exception('Error creating user. ' . $response->getReasonPhrase());
      }
    }
    catch (\Exception $e) {
      $this->logger = $this->container->get('monolog.logger.clipper');
      // Return operation specific error
      $returnObject['user'] = FALSE;
      $returnObject['error_message'] =  $e->getMessage();
      $responseStatus = 400;
      $this->logger->debug("Create account - General exception: {$e}");
    }

    return new Response($returnObject, $responseStatus);
  }
  
  /**
   * Edit user.
   *
   * @ApiDoc(
   *   resource=true,
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     400 = "Bad request or invalid data from the form",
   *   }
   * )
   *
   * The data is coming from an AJAX call performed on a 3rd party site
   * 
   * /api/users/USER_ID_XX
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   * @param string $uid - Id of a FWSSO user
   * 
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function postUserAction(ParamFetcher $paramFetcher, $uid)
  {
    $this->logger = $this->container->get('monolog.logger.clipper');
    // Object to return to remote form
    $returnObject = array();
    $responseStatus = 200;


    // Load user from JSON Web Token
    $usr = $this->get('security.context')->getToken()->getUser();
    // get user ID
    $userId = $usr->getUserId();
    $uid = $userId;
    
    // POST params
    $params = $this->getUserFields();
    $this->prepareParamFetcher($paramFetcher, $params);
    
    try {
      // get $_POST values
      $user = array();
      $user['uid'] = $uid;
      $data = $paramFetcher->all();
      // Map POST data to FWSSO API names
      foreach ($data as $param => $value) {
        if (isset($params[$param]['fwsso_name'])) {
          $user[$params[$param]['fwsso_name']] = $value;
        }
      }
      // FW SSO API - create user
      $fwsso_ws = $this->fwsso_ws();
      $response = $fwsso_ws->editUser($user);

      if ($response->isOk()) {
        $content = @json_decode($response->getContent(), TRUE);
        if (json_last_error() != JSON_ERROR_NONE) {
          // Return operation specific error
          $returnObject['user'] = FALSE;
          $admin_email = $this->container->getParameter('clipper.admin_email');
          $returnObject['error_message'] = 'An error has occurred. Please try again or contact us at <a href="mailto:' + $admin_email + '">' + $admin_email + '</a>.';
          $this->logger->debug('Edit profile - JSON decode error: ' . json_last_error());
          $responseStatus = 500;
        }
        $returnObject['user'] = $content;
        $returnObject['message'] = 'Your changes have been saved';
      } 
      else {
        throw new Exception('Error editing user. ' . $response->getReasonPhrase());
      }
    }
    catch (\Exception $e) {
      // Return operation specific error
      $returnObject['user'] = FALSE;
      $admin_email = $this->container->getParameter('clipper.admin_email');
      $returnObject['error_message'] = 'An error has occurred. Please try again or contact us at <a href="mailto:' + $admin_email + '">' + $admin_email + '</a>.';
      $responseStatus = 400;
      $this->logger->debug("Edit user - General exception: {$e}");
    }

    return new Response($returnObject, $responseStatus);
  }
  
  /**
   * Retrieve a User.
   *
   * @ApiDoc(
   *   resource=true,
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     204 = "No Content for the parameters passed"
   *   }
   * )
   * 
   * /api/users/USER_ID_XX
   *
   * @param Request $request the request object
   * @param string $uid - Id of a FWSSO user
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function getUserAction(Request $request, $uid)
  {
    // Object to return to remote form
    $returnObject = array();
    $responseStatus = 200;
    
    // Load user from JSON Web Token
    $usr = $this->get('security.context')->getToken()->getUser();
    // get user ID
    $userId = $usr->getUserId();
    
    $uid = $userId;
    
    try {
      // FW SSO API - retrive info
      $fwsso_ws = $this->fwsso_ws();
      $response = $fwsso_ws->getUser(array(
        'uid' => $uid
      ));

      if ($response->isOk()) {
        $content = @json_decode($response->getContent(), TRUE);
        if (json_last_error() != JSON_ERROR_NONE) {
          $this->logger = $this->container->get('monolog.logger.clipper');
          // Return operation specific error
          $returnObject['user'] = FALSE;
          $returnObject['error_message'] = 'An error has occurred. Please try again.';
          $this->logger->debug('Retrieve user - JSON decode error: ' . json_last_error());
          $responseStatus = 500;
        }
        $returnObject['user'] = $content;
        // Is this user invoice whitelisted?
        $returnObject['user']['whitelisted'] = $this->get('security.context')->isGranted('ROLE_INVOICE_WHITELISTED');
      } 
      else {
        throw new Exception('Error retrieving the user. ' . $response->getReasonPhrase());
      }
      
    }
    catch (\Exception $e) {
      $this->logger = $this->container->get('monolog.logger.clipper');
      // Return operation specific error
      $returnObject['product'] = FALSE;
      $returnObject['error_message'] =  $e->getMessage();
      $responseStatus = 400;
      $this->logger->debug("Retrieve user - General exception: {$e}");
    }

    return new Response($returnObject, $responseStatus);
  }
  
  
  /**
   * Forgot password.
   *
   * @ApiDoc(
   *   resource=true,
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     204 = "No Content for the parameters passed"
   *   }
   * )
   * 
   * /api/user/password
   *
   * @param Request $request the request object
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function getUserPasswordAction(Request $request)
  {
    // Object to return to remote form
    $returnObject = array();
    $responseStatus = 200;
    
    try {
      $user = array();
      $user['user'] = $request->query->get('email');
      
      $fwsso_ws = $this->fwsso_ws();
      $response = $fwsso_ws->forgotPassword($user);
      if ($response->isOk()) {
        $returnObject['message'] = $response;
      } 
      else {
        throw new Exception('Error retrieving the password. ' . $response->getReasonPhrase());
      }
      
    }
    catch (\Exception $e) {
      $this->logger = $this->container->get('monolog.logger.clipper');
      // Return operation specific error
      $returnObject['product'] = FALSE;
      $returnObject['error_message'] = 'An error has occurred. Please try again.';
      $responseStatus = 400;
      $this->logger->debug("Forgot password - General exception: {$e}");
    }

    return new Response($returnObject, $responseStatus);
  }
  
  /**
   * Change user password.
   *
   * @ApiDoc(
   *   resource=true,
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     204 = "No Content for the parameters passed"
   *   }
   * )
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @requestparam(name="username", default="", description="Username of the client.") 
   * @requestparam(name="password", default="", description="Password of the client.") 
   */
  public function postChangepasswordsAction(ParamFetcher $paramFetcher)
  {
    
    $container = $this->container;

    $username = $paramFetcher->get('username');
    $password = $paramFetcher->get('password');

    $retHeaders = array( 'Content-Type' => 'application/json' );

    // Get the user object, we need uid for change password
    $fwsso_ws = $this->fwsso_ws();
    $response = $fwsso_ws->getUser(array('uid'=>$username));
    if ($response->isOk()) {
      $user = @json_decode($response->getContent(), TRUE);
      if (json_last_error() != JSON_ERROR_NONE) {
        $this->logger = $this->container->get('monolog.logger.clipper');
        $this->logger->debug('Change password get user - JSON decode error: ' . json_last_error());
        // Return operation specific error
        $retObj = array(
          'error_message' => 'An error has occurred. Please try again.',
        );
        $retCode = 500;
        $response = new HttpFoundationResponse(json_encode($retObj), $retCode, $retHeaders);
        return $response;
      }
    } 
    else {
      throw new Exception('Error retrieving the password. ' . $response->getReasonPhrase());
    }

    // Change Password 
    if (isset($user['uid']) && !empty($user['uid'])) {
      $update_pass = array(
        'uid' => $user['uid'],
        'pass' => $password,
        'signature' => time(),
      );
    }

    $response = $fwsso_ws->changePassword($update_pass);
    if ($response->isOk()) {
      $content = @json_decode($response->getContent(), TRUE);
      if (json_last_error() != JSON_ERROR_NONE) {
        $this->logger = $this->container->get('monolog.logger.clipper');
        $this->logger->debug('Change password - JSON decode error: ' . json_last_error());
        // Return operation specific error
        $retObj = array(
          'error_message' => 'An error has occurred. Please try again.',
        );
        $retCode = 500;
        $response = new HttpFoundationResponse(json_encode($retObj), $retCode, $retHeaders);
        return $response;
      }
    } 
    else {
      throw new Exception('Error retrieving the password. ' . $response->getReasonPhrase());
    }

    $retObj = array(
      'message' => $content,
      'status' => 200,
    );
    $retHeaders = array( 'Content-Type' => 'application/json' );
    $retCode = 200;
    $response = new HttpFoundationResponse(json_encode($retObj), $retCode, $retHeaders);
    return $response;
  }

  /**
   * Send user password retrieval link.
   *
   * @ApiDoc(
   *   resource=true,
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     204 = "No Content for the parameters passed"
   *   }
   * )
   *  
   * @param ParamFetcher $paramFetcher Paramfetcher
   * 
   * @requestparam(name="email", default="", description="Email of the client.")
   */
  public function postForgotpasswordAction(ParamFetcher $paramFetcher)
  {
    // Object to return to remote form
    $returnObject = array();
    $responseStatus = 200;
    
    try {
      $container = $this->container;
      
      $email = $paramFetcher->get('email');
      
      $user = new FWSSOQuickLoginUser('', $email, '', array());
      $encKey = $container->getParameter('clipper.users.ql_encryptionkey');
      $ql_hash = $user->getQuickLoginHash($encKey);
      
      // @TODO Set the correct path
      $fe = $container->getParameter('clipper.frontend.url');
      $link = $fe . '#fpr?ql=' . $ql_hash;
  
      // @TODO Set the subject, from and body
      $msg = \Swift_Message::newInstance()
        ->setSubject('Recover password')
        ->setFrom('noreply@clipper.com')
        ->setTo($email)
        ->setBody($this->renderView('PSLClipperBundle:Clipper:forgotpassword.html.twig', array(
            'link' => $link
          )), 'text/html');
  
      $this->get('mailer')->send($msg);
      
      // return message
      $returnObject['message'] = 'Further instructions have been sent to "' . $email . '".';
    }
    catch (\Exception $e) {
      $this->logger = $this->container->get('monolog.logger.clipper');
      // Return operation specific error
      $returnObject['error_message'] =  'An error has occurred. Please try again.';
      $responseStatus = 400;
      $this->logger->debug("Forgot password - General exception: {$e}");
    }
    
    return new Response($returnObject, $responseStatus);
  }
  
  /**
   * ----------------------------------------------------------------------------------------
   * Helper functions
   * ----------------------------------------------------------------------------------------
   */
  
  private function prepareParamFetcher(&$paramFetcher, $params = array()) {
    foreach ($params as $param_name => $param_def) {
      $tmpParam = new RequestParam();
      $tmpParam->name = $param_name;
      if (isset($param_def['nullable']) && $param_def['nullable'] == TRUE) {
        $tmpParam->nullable = TRUE;
      }
      $paramFetcher->addParam($tmpParam);
    }
  }

  private function getUserFields() {
    return array(
      'username' => array(
        'fwsso_name' => 'name'
      ),
      'mail' => array(
        'fwsso_name' => 'mail'
      ),
      'pass' => array(
        'fwsso_name' => 'pass',
        'nullable' => TRUE
      ),
      'timezone' => array(
        'fwsso_name' => 'timezone',
        'nullable' => TRUE
      ),
      'firstname' => array(
        'fwsso_name' => 'field_firstname'
      ),
      'lastname' => array(
        'fwsso_name' => 'field_lastname'
      ),
      'country' => array(
        'fwsso_name' => 'field_country'
      ),
      'company' => array(
        'fwsso_name' => 'field_company'
      ),
      'title' => array(
        'fwsso_name' => 'field_title'
      ),
      'jobfunction' => array(
        'fwsso_name' => 'field_job_function'
      ),
      'salutation' => array(
        'fwsso_name' => 'field_salutation',
        'nullable' => TRUE
      ),
      'telephone' => array(
        'fwsso_name' => 'field_phone',
        'nullable' => TRUE
      ),
    );
  }

}
