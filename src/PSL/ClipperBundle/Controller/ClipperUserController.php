<?php
/**
 * Main Clipper User Controller
 */

namespace PSL\ClipperBundle\Controller;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;
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
    // @TODO: get from settings
    $settings['fwsso_baseurl'] = $this->container->getParameter('fwsso_baseurl');
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
   * /api/users
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function postUsersAction(ParamFetcher $paramFetcher) 
  {
    
    // Object to return to remote form
    $returnObject = array();
    $responseStatus = 200;
    
    try {
      
      // get $_POST values
      $user = array();
      $user['username'] = $paramFetcher->get('username');
      
      // FW SSO API - create user
      $fwsso_ws = $this->fwsso_ws();
      
      // do something with data
      
      $returnObject = $fwsso_ws->createUser($user);
    }
    catch (\Exception $e) {
      // Return operation specific error
      $returnObject['product'] = FALSE;
      $returnObject['error_message'] =  $e->getMessage();
      $responseStatus = 400;
      $this->logger->debug("General exception: {$e}");
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
    
    // Object to return to remote form
    $returnObject = array();
    $responseStatus = 200;
    
    try {
      
      // get $_POST values
      $user = array();
      $user['username'] = $paramFetcher->get('username');
      
      // FW SSO API - edit user info
      
    }
    catch (\Exception $e) {
      // Return operation specific error
      $returnObject['product'] = FALSE;
      $returnObject['error_message'] =  $e->getMessage();
      $responseStatus = 400;
      $this->logger->debug("General exception: {$e}");
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
    
    try {
       
      $user = array();
      $user['id'] = $request->query->get('user_id');
      
      // FW SSO API - retrive info
      
    }
    catch (\Exception $e) {
      // Return operation specific error
      $returnObject['product'] = FALSE;
      $returnObject['error_message'] =  $e->getMessage();
      $responseStatus = 400;
      $this->logger->debug("General exception: {$e}");
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
      $user['email'] = $request->query->get('email');
      
      
      // FW SSO API - forgot password
      
    }
    catch (\Exception $e) {
      // Return operation specific error
      $returnObject['product'] = FALSE;
      $returnObject['error_message'] =  $e->getMessage();
      $responseStatus = 400;
      $this->logger->debug("General exception: {$e}");
    }

    return new Response($returnObject, $responseStatus);
  }
  
  
  /**
   * ----------------------------------------------------------------------------------------
   * Helper functions
   * ----------------------------------------------------------------------------------------
   */
  
  
  
  
}
