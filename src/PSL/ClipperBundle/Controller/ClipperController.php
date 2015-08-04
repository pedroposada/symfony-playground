<?php
/**
 * Main Clipper Controller
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
use Symfony\Component\Config\FileLocator;

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
use Stripe\Stripe;

// custom
use PSL\ClipperBundle\Utils\LimeSurvey as LimeSurvey;
use PSL\ClipperBundle\Entity\Repository\FirstQProjectRepository;
use PSL\ClipperBundle\Entity\FirstQProject as FirstQProject;
use PSL\ClipperBundle\Service\GoogleSpreadsheetService;
use PSL\ClipperBundle\Service\SurveyBuilderService;

use \stdClass as stdClass;
use \Exception as Exception;

/**
 * Rest Controller for Clipper
 */
class ClipperController extends FOSRestController
{
  
  /**
   * ----------------------------------------------------------------------------------------
   * API
   * ----------------------------------------------------------------------------------------
   */

  /**
   * Autocomplete callback for input text field
   *
   * @ApiDoc(
   *   resource=true,
   *   statusCodes = {
   *     200 = "Returned when successful",
   *   }
   * )
   *
   * The data is coming from an AJAX call performed on a 3rd party site
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @QueryParam(name="group", default="brands", description="Name of group of keywords, used in file name")
   * @QueryParam(name="keyword", default="", description="User input text in autocomplete field")
   * 
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function getClipperAutocompleteAction(ParamFetcher $paramFetcher)
  {
    $group = $paramFetcher->get('group');
    $result = array();
    $file = $this->container->getParameter('clipper.' . $group);
    $kernel = $this->container->get('kernel');
    $path = $kernel->locateResource("@PSLClipperBundle/{$file}");
    $input = $paramFetcher->get('keyword');
    
    if (!empty($input)) {
      $doc = new \DOMDocument();
      $doc->preserveWhiteSpace = false;
      $doc->Load($path);
      $xpath = new \DOMXPath($doc);
      
      // returns first 20 items
      $input = strtolower($input);
      $expression = '(//field[starts-with(translate(., "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "'. $input .'")])[position() <= 20]';
      $results = $xpath->query($expression);
      foreach ($results as $node) {
        $result[] = $node->nodeValue;
      }
    }
    
    return new Response($result);
  }

  /**
   * Inserts a new FristQ order.
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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @requestparam(name="loi", default="", description="LOI number.")
   * @requestparam(name="ir", default="", description="IR number.")
   * @requestparam(name="title", default="", description="Title, user generated.")
   * @requestparam(name="name", default="", description="Name of the folio.")
   * @requestparam(name="name_full", default="", description="Full name of the folio, user generated (same as Title).")
   * @requestparam(name="patient_type", default="", description="Patient type, user generated..")
   * @requestparam(name="num_participants", default="", description="Number of participants.")
   * @requestparam(name="market", default="", description="Market array.", array=true)
   * @requestparam(name="specialty", default="", description="Specialty array.", array=true)
   * @requestparam(name="timestamp", default="", description="Timestamp.")
   * @requestparam(name="survey_brand", default="", description="Brand array.", array=true)
   * @requestparam(name="statement", default="", description="Statement array.", array=true)
   * @requestparam(name="firstq_uuid", default="", description="FirstQ project uuid.")
   * @requestparam(name="launch_date", default="", description="Lauch date of the folio.")
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function postOrderAction(ParamFetcher $paramFetcher)
  {
    $this->logger = $this->container->get('monolog.logger.clipper');

    // Object to return to remote form
    $returnObject = array();
    $responseStatus = 200;
    
    try {
      // get $_POST values
      $form_data = new stdClass();
      $form_data->loi = 10; // hard coded for now
      $form_data->ir = 10; // hard coded for now
      $form_data->title = $paramFetcher->get('title');
      $form_data->name = $paramFetcher->get('name');
      $form_data->name_full = $paramFetcher->get('name_full');
      $form_data->patient_type = $paramFetcher->get('patient_type');
      $form_data->num_participants = 35; // hard coded for now
      $form_data->timestamp = $paramFetcher->get('timestamp');
      $form_data->markets = $paramFetcher->get('market');
      $form_data->specialties = $paramFetcher->get('specialty');
      $form_data->brands = $paramFetcher->get('survey_brand');
      $form_data->statements = $paramFetcher->get('statement');
      $form_data->launch_date = $paramFetcher->get('launch_date');
      $firstq_uuid = $paramFetcher->get('firstq_uuid');
      
      // Google Spreadsheet validation
      $gsc = $this->get('google_spreadsheet');
      $gsc->setupFeasibilitySheet();
      
      $gs_result_array = array();
      $gs_result_total = 0;

      foreach ( $form_data->markets as $market_key => $market_value ) {
        foreach ( $form_data->specialties as $specialty_key => $specialty_value ) {
          $form_data_object = new stdClass();
          $form_data_object->loi = 10; // hard coded for now
          $form_data_object->ir = 10; // hard coded for now
          $form_data_object->market = $market_value;
          $form_data_object->specialty = $specialty_value;
          // check feasibility
          $gs_result = $gsc->requestFeasibility($form_data_object);
          // add results
          if( $gs_result ) {
            $gs_result_array[] = $gs_result;
            $gs_result_total += str_replace(',', '', $gs_result->price);
          }
        }
      }
      
      // Save or update into the database
      $firstq_uuid = $this->createFirstQProject(serialize($form_data), serialize($gs_result_array), $firstq_uuid);
      
      // build response
      // product
      $returnObject['product']['price'] = 4995; //number_format(4995, 2, '.', ','); // Hardcoded for now
      $returnObject['product']['firstq_uuid'] = $firstq_uuid;
      // worldpay parameters for the front end form
      // $parameters_clipper = $this->container->getParameter('clipper');
      // $returnObject['worldpay']['inst_id'] = $parameters_clipper['worldpay']['inst_id'];
      // $returnObject['worldpay']['form_action'] = $parameters_clipper['worldpay']['form_action'];
      // $returnObject['worldpay']['test_mode'] = $parameters_clipper['worldpay']['test_mode'];
      // $returnObject['worldpay']['cart_id'] = $parameters_clipper['worldpay']['cart_id'];
      
    }
    catch (\Doctrine\ORM\ORMException $e) {
      // ORM exception

      $returnObject['product'] = FALSE;
      $returnObject['error_message'] = $e->getMessage();
      $responseStatus = 400;
      $this->logger->debug("Doctrine exception: {$e}");
    }
    catch (\Exception $e) {
      // Return operation specific error
      $returnObject['product'] = FALSE;
      $returnObject['error_message'] = $e->getMessage();
      $responseStatus = 400;
      $this->logger->debug("General exception: {$e}");
    }

    return new Response($returnObject, $responseStatus);
  }
  
  /**
   * Retrieve FirstQ orders.
   *
   * @ApiDoc(
   *   resource=true,
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     204 = "No Content for the parameters passed"
   *   }
   * )
   *
   * @param Request $request the request object
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function getOrdersAction(Request $request) {
    
    // get user_id from request
    $user_id = $request->query->get('user_id');
    
    $em = $this->getDoctrine()->getManager();
    $firstq_projects = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQProject')->findByUserId($user_id);
    
    if (!$firstq_projects->isEmpty()) {
      $firstqs_formatted = array(); 
      foreach ($firstq_projects as $key => $firstq_project) {
        $firstqs_formatted[] = $firstq_project->getFormattedFirstQProject();
      }
      return new Response($firstqs_formatted);
    }
    else {
      $message = 'No orders for this user.';
      return new Response($message, 204); // No Content
    }
  }
  
  /**
   * Retrieve a FirstQ order.
   *
   * @ApiDoc(
   *   resource=true,
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     204 = "No Content for the parameters passed"
   *   }
   * )
   *
   * @param Request $request - the request object
   * @param string $uuid - Unique id of a FirstQ project
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function getOrderAction(Request $request, $uuid) {
    $em = $this->getDoctrine()->getManager();
    $firstq_project = $em->getRepository('PSLClipperBundle:FirstQProject')->find($uuid);
    
    if ($firstq_project) {
      $firstq_formatted = $firstq_project->getFormattedFirstQProject();
      return new Response($firstq_formatted);
    }
    else {
      $message = 'No order with this id: ' . $uuid;
      return new Response($message, 204); // no content
    }
  }
  
  /**
   * Process a FristQ Order
   *
   * @ApiDoc(
   *   resource=true,
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     400 = "Bad request or invalid data from the form",
   *   }
   * )
   *
   * The data is coming from an AJAX call performed on the front end
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @requestparam(name="stripeToken", default="", description="The Stripe token.")
   * @requestparam(name="firstq_uuid", default="", description="FirstQ project uuid.")
   * @requestparam(name="amount", default="", description="Amount of the project.")
   * @requestparam(name="email", default="", description="Email of the client.")
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function postOrderProcessAction(ParamFetcher $paramFetcher)
  {
    $this->logger = $this->container->get('monolog.logger.clipper');
    
    // Get parameters from the POST
    $firstq_uuid = $paramFetcher->get('firstq_uuid');
    $stripe_token = $paramFetcher->get('stripeToken');
    $amount = (int)$paramFetcher->get('amount') * 100; // in cents
    $email = $paramFetcher->get('email'); // not necessary
    
    // return error if empty
    if (empty($firstq_uuid) || empty($stripe_token)) {
      $message = 'Invalid request - missing parameters';
      return new Response($message, 400); // invalid request
    }
    
    // Validate if firstq exists and is not processed yet
    $em = $this->getDoctrine()->getManager();
    $firstq_project = $em->getRepository('PSLClipperBundle:FirstQProject')->find($firstq_uuid);
    if (empty($firstq_project) || $firstq_project->getState() != 'ORDER_PENDING') {
      $message = 'Error - FirstQ uuid is invalid';
      return new Response($message, 400);
    }
    
    // create the charge on Stripe's servers
    // this will charge the user's card
    try {
      
      $parameters_clipper = $this->container->getParameter('clipper');
      
      // initiate the Stripe  and charge
      \Stripe\Stripe::setApiKey($parameters_clipper['stripe']['private_key']);
      $charge = \Stripe\Charge::create(array(
        "amount" => $amount, // amount in cents, again
        "currency" => "usd",
        "source" => $stripe_token,
        "description" => $email
        )
      );
      
      // Check that it was paid:
      if ($charge->paid == true) {
        
        // change status to order complete and return ok for redirect
        $firstq_project->setState($parameters_clipper['state_codes']['order_complete']);
        $em->persist($firstq_project);
        $em->flush();
        
        $firsq['fquuid'] = $firstq_uuid;
        return new Response($message, 200);
      } 
      else {
        // failed
        $this->logger->debug('Payment System Error. Payment could NOT be processed. Not paid.');
        
        $message = 'Payment System Error! Your payment could NOT be processed (i.e., you have not been charged) because the payment system rejected the transaction. You can try again or use another card.';
        return new Response($message, 400); // no content
      }
    } 
    catch (\Stripe\Error\Card $e) {
      // Card was declined.
      $e_json = $e->getJsonBody();
      $err = $e_json['error'];
      $errors['stripe'] = $err['message'];
      
      $this->logger->debug("Stripe/Card exception: {$e}");
      
      $message = 'Card was declined.';
      return new Response($message, 400); // no content
    } 
    catch (\Stripe\Error\ApiConnection $e) {
      // Network problem, perhaps try again.
      $this->logger->debug("Stripe/ApiConnection exception: {$e}");
      
      $message = 'Network problem, perhaps try again.';
      return new Response($message, 400); // no content
    } 
    catch (\Stripe\Error\InvalidRequest $e) {
      // You screwed up in your programming. Shouldn't happen!
      $this->logger->debug("Stripe/InvalidRequest exception: {$e}");
      
      $message = 'Invalid request';
      return new Response($message, 400); // no content
    } 
    catch (\Stripe\Error\Api $e) {
      // Stripe's servers are down!
      $this->logger->debug("Stripe/Api exception: {$e}");
      
      $message = 'Network problem, perhaps try again.';
      return new Response($message, 400); // no content
    } 
    catch (\Stripe\Error\Base $e) {
      // Something else that's not the customer's fault.
      $this->logger->debug("Stripe/Base exception: {$e}");
      
      $message = 'Error - Please try again.';
      return new Response($message, 400); // no content
    }
    
  }
  
  /**
   * ----------------------------------------------------------------------------------------
   * HELPERS
   * ----------------------------------------------------------------------------------------
   */

  /**
   * Saves a new FirstQProject or update an existing one
   *
   * @param string $form_data_serialized - data from the form
   * @param string $gs_result_serialized - data from google speadsheet
   * @param string $firstq_uid - first q unique id, can be null
   */
  private function createFirstQProject($form_data_serialized, $gs_result_serialized, $firstq_uuid)
  {
    // Get parameters
    $parameters_clipper = $this->container->getParameter('clipper');
    $em = $this->getDoctrine()->getManager();
    
    $firstq_project;
    
    if (!empty($firstq_uuid)) {
      // Check if object exists already
      $firstq_project = $em->getRepository('PSLClipperBundle:FirstQProject')->find($firstq_uuid);
      if (!$firstq_project) {
        // Create FirstQProject entity
        $firstq_project = new FirstQProject();
      }
    }
    else {
      // Create FirstQProject entity
      $firstq_project = new FirstQProject();
    }
    
    // from form
    $firstq_project->setFormDataRaw($form_data_serialized);
    // from GoogleSheet
    $firstq_project->setSheetDataRaw($gs_result_serialized);
    // set initial state
    $firstq_project->setState($parameters_clipper['state_codes']['order_pending']);
    
    $em->persist($firstq_project);
    $em->flush();
    
    return $firstq_project->getId();
  }
  
  /**
   * Simple debug output
   * /clipper/autocomplete
   */
  public function autocompleteAction()
  {
    $response = $this->render('PSLClipperBundle:Clipper:autocomplete.html.twig');

    return $response;
  }
  
  /**
   * ----------------------------------------------------------------------------------------
   * REDIRECT OR OUPUT
   * ----------------------------------------------------------------------------------------
   */

  /**
   * redirect users to LimeSurvey Survey page
   * /clipper/limesurvey/{sid}/{slug}/{lang}
   */
  public function redirectLimeSurveyAction($sid, $slug, $lang)
  {
    $response = null;

    // config connection to LS
    $params_ls = $this->container->getParameter('limesurvey');
    $ls = new LimeSurvey();
    $ls->configure($params_ls['api']);
    $response = $ls->get_survey_properties(array(
      'iSurveyID' => $sid,
      'aSurveySettings' => array('expires'),
    ));

    if( isset($response['expires']) && !is_null($response['expires']) ) {
      // display message
      return $this->render('PSLClipperBundle:Clipper:maximum.html.twig');
    }
    else {
      // redirect
      $limesurvey_url_destination = $this->container->getParameter('limesurvey.url_destination');
      $destination = strtr($limesurvey_url_destination, array(
        '[SID]' => $sid,
        '[LANG]' => 'en',
        '[SLUG]' => $slug
      ));
      return new RedirectResponse($destination, 301);
      // http status code 301 Moved Permanently
    }
  }

  /**
   * Simple debug output
   * /debug
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   */
  public function debugAction(ParamFetcher $paramFetcher)
  {
    // comment out line below to override display of dump(request)
    $debug = dump($paramFetcher);
    // $debug = 'any output';
    // $debug = dump($debug);
    return $this->render('PSLClipperBundle:Clipper:debug.html.twig', array('debug' => $debug));
  }

  /**
   * Simple debug output
   * /clipper/limesurvey/exit
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @QueryParam(name="lstoken", default="(empty)", description="LimeSurvey
   * Participant Token.")
   */
  public function exitAction(ParamFetcher $paramFetcher)
  {
    $response = $this->render('PSLClipperBundle:Clipper:exit.html.twig', array('token' => $paramFetcher->get('lstoken')));

    return $response;
  }
  
  /**
   * flag order as order_complete and redirect to front-end
   * /clipper/thankyou/{fquuid}
   * 
   * @param string $fquuid FirstQ uuid
   */
  public function thankyouAction($fquuid)
  {
    
    // @TODO: this might no longer be needed since the front end will handle it
    
    $parameters_clipper = $this->container->getParameter('clipper');
    $em = $this->getDoctrine()->getManager();
    $firstq_project = $em->getRepository('PSLClipperBundle:FirstQProject')->find($fquuid);
    
    if ($firstq_project && $firstq_project->getState() == 'ORDER_PENDING') {
      
      $firstq_project->setState($parameters_clipper['state_codes']['order_complete']);
      $em->persist($firstq_project);
      $em->flush();
      
      $clipper_frontend_url = $this->container->getParameter('clipper.frontend.url');
      $destination = "{$clipper_frontend_url}?fquuid={$fquuid}&destination=dashboard";
    }
    else {
      $debug = 'Error - FirstQ uuid is invalid';
      return $this->render('PSLClipperBundle:Clipper:debug.html.twig', array('debug' => $debug));
    }
    
    return new RedirectResponse($destination, 301);
    
  }
  
}
