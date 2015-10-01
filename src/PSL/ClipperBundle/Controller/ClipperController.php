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

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

// custom
use PSL\ClipperBundle\Utils\LimeSurvey as LimeSurvey;
use PSL\ClipperBundle\Entity\Repository\FirstQProjectRepository;
use PSL\ClipperBundle\Entity\FirstQGroup as FirstQGroup;
use PSL\ClipperBundle\Entity\FirstQProject as FirstQProject;
use PSL\ClipperBundle\Entity\FirstQProcessAction as FirstQProcessAction;
use PSL\ClipperBundle\Service\GoogleSpreadsheetService;
use PSL\ClipperBundle\Service\SurveyBuilderService;

use \stdClass as stdClass;
use \Exception as Exception;
use \DateTime as DateTime;
use \DateTimeZone as DateTimeZone;

/**
 * Rest Controller for Clipper
 */
class ClipperController extends FOSRestController
{

  protected function getSerializer()
  {
    $encoders = array(new XmlEncoder(), new JsonEncoder());
    $normalizers = array(new ObjectNormalizer());

    return new Serializer($normalizers, $encoders);
  }

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
   * @requestparam(name="name", default="", description="Readable name of the folio.")
   * @requestparam(name="survey_type", default="", description="Machine name of the folio.")
   * @requestparam(name="patient_type", default="", description="Patient type, user generated..")
   * @requestparam(name="num_participants", default="", description="Number of participants.")
   * @requestparam(name="market", default="", description="Market array.", array=true)
   * @requestparam(name="specialty", default="", description="Specialty array.", array=true)
   * @requestparam(name="timestamp", default="", description="Timestamp.")
   * @requestparam(name="survey_brand", default="", description="Brand array.", array=true)
   * @requestparam(name="attribute", default="", description="Attribute array.", array=true)
   * @requestparam(name="firstq_uuid", default="", description="FirstQ project uuid.")
   * @requestparam(name="launch_date", default="", description="Lauch date of the folio.")
   * @requestparam(name="timezone_client", default="", description="Timezone of the client.")
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function postNeworderAction(ParamFetcher $paramFetcher)
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
      $form_data->name = $paramFetcher->get('name'); // used for limesurvey creation
      $form_data->survey_type = $paramFetcher->get('survey_type');
      $form_data->patient_type = $paramFetcher->get('patient_type');
      $form_data->num_participants = 35; // hard coded for now
      $form_data->timestamp = $paramFetcher->get('timestamp');
      $form_data->markets = $paramFetcher->get('market');
      $form_data->specialties = $paramFetcher->get('specialty');
      $form_data->brands = $paramFetcher->get('survey_brand');
      $form_data->attributes = $paramFetcher->get('attribute');
      $form_data->launch_date = $paramFetcher->get('launch_date'); // Y-m-d H:i:s
      $form_data->timezone_client = $paramFetcher->get('timezone_client');
      $firstq_group_uuid = $paramFetcher->get('firstq_uuid');

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
      $firstq_uuid = $this->createFirstQProject($form_data, $gs_result_array, $firstq_group_uuid);

      // build product response
      $returnObject['product']['price'] = 4995; //number_format(4995, 2, '.', ','); // Hardcoded for now
      $returnObject['product']['firstq_uuid'] = $firstq_uuid;

      // calculate estimated time of completion
      $timezone_adjusment = $this->latestTimezoneAndAdjustment($form_data->markets, $form_data->specialties);
      $completion_date = $this->calculateSurveyCompletionTime($form_data->launch_date, $form_data->timezone_client, $timezone_adjusment);

      $returnObject['product']['end_date'] = $completion_date;
      $returnObject['product']['firstq_uuid'] = $firstq_uuid;
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
  public function getOrdersAction(Request $request)
  {

    $params = $this->container->getParameter('clipper');
    $em = $this->getDoctrine()->getManager();

    $firstq_groups;

    // @TODO: get orders according to the user's session
    // either by retrieving a user's ID in the JWT token
    // or with the FW SSO through loadUserByUsername

    // get user_id from request
    $user_id = $request->query->get('user_id');
    if (!empty($user_id)) {
      $firstq_groups = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQGroup')->findByUserId($user_id);
    }

    if (!empty($firstq_groups)) {
      $firstqs_formatted = array();
      foreach ($firstq_groups as $key => $firstq_group) {
        $firstqs_formatted[] = $firstq_group->getFormattedFirstQGroup();
      }
      return new Response($firstqs_formatted);
    }
    else {
      $message = 'No orders.';
      return new Response($message, 204); // No Content
    }
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
  public function getOrdersAdminAction(Request $request)
  {

    $this->logger = $this->container->get('monolog.logger.clipper');

    if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMINUI_USER')) {
      throw $this->createAccessDeniedException();
    }

    $params = $this->container->getParameter('clipper');
    $em = $this->getDoctrine()->getManager();

    try {

      $firstq_groups;

      $status = $request->query->get('status');
      if (!empty($status)) {
        $firstq_groups = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQGroup')->findByState($params['state_codes'][$status]);
      }

      if (!empty($firstq_groups)) {
        $firstqs_formatted = array();
        foreach ($firstq_groups as $key => $firstq_group) {

          // User info retrieval from the FW SSO
          $fwsso_config = $this->container->getParameter('fwsso_api');
          $settings['fwsso_baseurl'] = $fwsso_config['url'];
          $settings['fwsso_app_token'] = $fwsso_config['app_token'];

          $fwsso_ws = $this->container->get('fw_sso_webservice');
          $fwsso_ws->configure($settings);
          $response = $fwsso_ws->getUser(array('uid' => $firstq_group->getUserId()));

          $user_info = array();

          if ($response->isOk()) {

            $content = @json_decode($response->getContent(), TRUE);
            if (json_last_error() != JSON_ERROR_NONE) {
              throw new Exception('JSON decode error: ' . json_last_error());
            }

            // User info
            $user_info['username'] = $content['field_firstname']['und'][0]['value'] . " " . $content['field_lastname']['und'][0]['value'];
            $user_info['address'] = $content['name']; // email
            $user_info['phone'] = $content['field_company']['und'][0]['value'] . '<br/>' . $content['field_phone']['und'][0]['value'];
            $user_info['company_name'] = $content['field_company']['und'][0]['value'];
          }

          $processed_info = NULL;
          if ($params['state_codes'][$status] == $params['state_codes']['order_declined']) {

            $firstq_process = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQProcessAction')
                                   ->findOneBy(array('groupUuid' => $firstq_group->getId()));

            $processed_info = array();
            if ($firstq_process) {
              $processed_info['username'] = $firstq_process->getUsername();
              $processed_info['updated'] = $firstq_process->getUpdated();

            }
            else {
              $processed_info['username'] = $firstq_group->getId();
              $processed_info['updated'] = 'now';
            }
          }

          $firstqs_formatted[] = $firstq_group->getFormattedFirstQGroup($user_info, $processed_info);
        }
        return new Response($firstqs_formatted);
      }
      else {
        $message = 'No orders.';
        return new Response($message, 204); // No Content
      }
    }
    catch (\Exception $e) {
      // Something messed up
      $this->logger->debug("exception: {$e}");
      $message = 'Error - Please try again.';
      return new Response($message, 400); // Error
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
  public function getOrderAction(Request $request, $uuid)
  {
    $em = $this->getDoctrine()->getManager();
    $firstq_group = $em->getRepository('PSLClipperBundle:FirstQGroup')->find($uuid);

    if ($firstq_group) {
      $firstq_formatted = $firstq_group->getFormattedFirstQGroup();
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
   * @requestparam(name="payment_method_nonce", default="", description="The Braintree payment nonce.")
   * @requestparam(name="firstq_uuid", default="", description="FirstQ project uuid.")
   * @requestparam(name="amount", default="", description="Amount of the project.")
   * @requestparam(name="email", default="", description="Email of the client.")
   * @requestparam(name="method", default="", description="Payment method.")
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function postOrderProcessAction(ParamFetcher $paramFetcher)
  {
    $this->logger = $this->container->get('monolog.logger.clipper');

    // Get parameters from the POST
    $firstq_group_uuid = $paramFetcher->get('firstq_uuid');

    // payment_method_nonce
    $payment_method_nonce = $paramFetcher->get('payment_method_nonce');

    $amount = '100.00'; // string ex: 100.00
    $email = $paramFetcher->get('email'); // not necessary
    $method = $paramFetcher->get('method'); // not necessary

    // return error if empty
    if (empty($firstq_group_uuid) || empty($payment_method_nonce)) {
      $message = 'Invalid request - missing parameters';
      return new Response($message, 400); // invalid request
    }

    // Validate if firstq exists and is not processed yet
    $em = $this->getDoctrine()->getManager();
    $firstq_group = $em->getRepository('PSLClipperBundle:FirstQGroup')->find($firstq_group_uuid);
    if (empty($firstq_group) || $firstq_group->getState() != 'ORDER_PENDING') {
      $message = 'Error - FirstQ uuid is invalid';
      return new Response($message, 400);
    }

    $parameters_clipper = $this->container->getParameter('clipper');

    if ($method == 'INVOICE') {
      if ($this->get('security.context')->isGranted('ROLE_INVOICE_WHITELISTED')) {
        $firstq_group->setState($parameters_clipper['state_codes']['order_complete']);
        $em->persist($firstq_group);
        $em->flush();

        $message = 'Order complete. Your payment will be via invoice.';
        return new Response($message, 200);
      } else {
        $firstq_group->setState($parameters_clipper['state_codes']['order_invoice']);
        $em->persist($firstq_group);
        $em->flush();

        $message = 'Order pending. The order will be activated after payment.';
        return new Response($message, 200);
      }
    }

    // create the charge on Stripe's servers
    // this will charge the user's card
    try {
      $parameters_clipper = $this->container->getParameter('clipper');


      // @TODO: Use proper config according to country

      \Braintree_Configuration::environment('sandbox');
      \Braintree_Configuration::merchantId('56pc8bpms5mqfdsz');
      \Braintree_Configuration::publicKey('7pys8m43bxfp56k9');
      \Braintree_Configuration::privateKey('414b76ed3e23cca45dbacfb78da0ddf6');

      $sale_params = array(
        'amount' => $amount,
        'paymentMethodNonce' => $payment_method_nonce
      );

      $result = \Braintree_Transaction::sale($sale_params);

      // Check that it was paid:
      if ($result->success == true) {

      // TODO: CLIP-30.
      // # Credit card
      // 1. send email to client with sale’s info.
      // 2. send email to FW Finance about the new sale with sale’s info.
      //
      // # Invoice and Points
      // 1. send email to client with sale’s info and a message that the order
      //    will be aproved.
      // 2. send email to FW Finance about the new sale with sale’s info AND
      //    also a link to the Admin UI for approval.
      //
      // # Redirect
      // Redirect to Dashboard Active.

      // Check that it was paid:
        // change status to order complete and return ok for redirect
        $firstq_group->setState($parameters_clipper['state_codes']['order_complete']);
        $firstq_group->setOrderId($result->transaction->id);
        $em->persist($firstq_group);
        $em->flush();

        $firsq['fquuid'] = $firstq_group_uuid;
        return new Response($firsq, 200);
      }
      else {
        // failed
        $this->logger->debug('Payment System Error. Payment could NOT be processed. Not paid.');

        $message = 'Payment System Error! Your payment could NOT be processed (i.e., you have not been charged) because the payment system rejected the transaction. You can try again or use another card.';
        return new Response($message, 400); // no content
      }
    }
    catch (\Exception $e) {
      // Something messed up
      $this->logger->debug("exception: {$e}");
      $message = "Error - Please try again. {$e}";
      return new Response($message, 400); // Error
    }

  }

  /**
   * Process a FristQ Order on the Admin side
   *
   * @ApiDoc(
   *   resource=true,
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     400 = "Bad request or invalid data from the form",
   *     401 = "Unauthorized request",
   *   }
   * )
   *
   * The data is coming from an AJAX call performed on the front end
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @requestparam(name="firstq_uuid", default="", description="FirstQ project uuid.")
   * @requestparam(name="task", default="", description="Task for invoice.")
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function postOrderAdminprocessAction(ParamFetcher $paramFetcher)
  {
    $this->logger = $this->container->get('monolog.logger.clipper');

    if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMINUI_USER')) {
        throw $this->createAccessDeniedException();
    }

    // Get parameters from the POST
    $firstq_group_uuid = $paramFetcher->get('firstq_uuid');
    $task = $paramFetcher->get('task');

    // return error if empty
    if (empty($firstq_group_uuid) || empty($task)) {
      $message = 'Invalid request - missing parameters';
      return new Response($message, 400); // invalid request
    }

    // Validate if firstq exists and is not processed yet
    $em = $this->getDoctrine()->getManager();
    $firstq_group = $em->getRepository('PSLClipperBundle:FirstQGroup')->find($firstq_group_uuid);

    if (empty($firstq_group) || $firstq_group->getState() != 'ORDER_INVOICE') {
      $message = 'Error - FirstQ uuid is invalid';
      return new Response($message, 400);
    }

    // Change status and record the action
    try {

      $parameters_clipper = $this->container->getParameter('clipper');

      $order_status;

      if ($task == 'accept') {
        $order_status = $parameters_clipper['state_codes']['order_complete'];
        // change status to order complete and return ok for redirect
        $firstq_group->setState($order_status);
        $em->persist($firstq_group);

        // Send email to client with sale’s info and message saying it was
        // approved.
        // TODO: CLIP-30. Find out how to get email addresses.
        $this->sendConfirmationEmail(
          'Confirmation email subject',
          'send@example.com',
          'receipient@example.com',
          'order_approved.client_copy',
          array()
        );
      }
      else {
        $order_status = $parameters_clipper['state_codes']['order_declined'];
        // change status to order complete and return ok for redirect
        $firstq_group->setState($order_status);
        $em->persist($firstq_group);
      }

      $usr = $this->get('security.context')->getToken()->getUser();
      $username = $usr->getUsername();

      // Create a new Process action
      $firstq_process = new FirstQProcessAction();
      $firstq_process->setGroupUuid($firstq_group_uuid);
      $firstq_process->setUsername($username);
      $firstq_process->setState($order_status);
      $em->persist($firstq_process);

      $em->flush();

      $firsq['fquuid'] = $firstq_group_uuid;
      return new Response($firsq, 200);
    }
    catch (\Exception $e) {
      // Something messed up
      $this->logger->debug("exception: {$e}");
      $message = 'Error - Please try again.';
      return new Response($message, 400); // Error
    }

  }


    /**
   * Get a Client Token for Braintree.
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
  public function getClientTokenAction(Request $request)
  {
    // Set Braintree configuration
    \Braintree_Configuration::environment('sandbox');
    \Braintree_Configuration::merchantId('56pc8bpms5mqfdsz');
    \Braintree_Configuration::publicKey('7pys8m43bxfp56k9');
    \Braintree_Configuration::privateKey('414b76ed3e23cca45dbacfb78da0ddf6');

    // get user_id from request
    //$firstq_uuid = $request->query->get('firstq_uuid');

    $client_token['clientToken'] = \Braintree_ClientToken::generate();

    if (!$client_token) {
      $message = 'Error - Please try again.';
      return new Response($message, 400); // Error
    }
    return new Response($client_token, 200);
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
   * @param string $gs_result_array - array of data from google speadsheet
   * @param string $firstq_uid - first q unique id, can be null
   */
  private function createFirstQProject($form_data_serialized, $gs_result_array, $firstq_group_uuid)
  {
    // Get parameters
    $parameters_clipper = $this->container->getParameter('clipper');
    $em = $this->getDoctrine()->getManager();

    // Check if object exists already
    if (!empty($firstq_group_uuid)) {
      $firstq_group = $em->getRepository('PSLClipperBundle:FirstQGroup')->find($firstq_group_uuid);

      if (!$firstq_group) {
        // Create FirstQGroup entity
        $firstq_group = new FirstQGroup();
      }
      else {
        // delete all projects associated to it
        $query = $em->createQuery('delete from PSLClipperBundle:FirstQProject fg where fg.group_uuid = :group_uuid')
          ->setParameter('group_uuid', $firstq_group->getId());
        $query->execute();
      }
    }
    else {
      // Create FirstQGroup entity
      $firstq_group = new FirstQGroup();
    }

    $firstq_group->setFormDataRaw($this->getSerializer()->encode($form_data_serialized, 'json'));
    $firstq_group->setState($parameters_clipper['state_codes']['order_pending']);
    $em->persist($firstq_group);

    // Loop for all combination and set individual FirstQ projects
    foreach ($gs_result_array as $key => $gs_result) {
      $firstq_project = new FirstQProject();
      $firstq_project->setSheetDataRaw($this->getSerializer()->encode($gs_result, 'json'));
      $firstq_project->setState($parameters_clipper['state_codes']['limesurvey_pending']);
      $firstq_project->setFirstqgroup($firstq_group);
      $em->persist($firstq_project);
    }

    $em->flush();

    return $firstq_group->getId();
  }

  /**
   * Calculation of Estimated completion time of a survey
   * This value is just an estimate and not the real time of completion
   *
   * @param timestamp $launch_date - timestamp of 'Y-m-d H:i:s'
   * @param timezone $timezone_client - timezone of client
   * @param array $timezone_adjusment - timezone of latest market
   * and adjustment longest time adjustment according to specialty/country
   *
   * @return formatted string date
   */
  private function calculateSurveyCompletionTime($launch_date, $timezone_client, $timezone_adjusment)
  {

    /**
     * launch_date = (Survey start Date/time)
     * start_time = (Time from start date/time where the slowest selected geography hits 8:00 am on a weekday)
     * estimation = (determine estimated completion time by region/specialty)
     *
     * ex:
     *
     * client in UK chooses now to start the survey US/Oncology
     *
     * launch_date = now (thursday 23 july, 3pm UK time)
     * tomorrow at 8 am of US
     *
     * time of estimated survey ~ 2 hours for 35 people
     *
     * 8 am + 2 hours = 10am + 6 hours timezone
     * completion time = 4pm, 24 july
     *
     *
     *
     * client in UK chooses in 4 days start at 3pm US/Oncology
     *
     * launch_date = sunday 27 july, 3pm UK time
     * monday 28 at 8 am of US
     *
     * time of estimated survey ~ 2 hours for 35 people
     *
     * 8 am + 2 hours = 10am + 6 hours timezone
     * completion time = 4pm, 28 july
     *
     * // fake data
     * // $launch_date = '2015-07-24 08:32:27';
     * // $timezone_latest = new DateTimeZone('America/New_York');
     * // $timezone_client = new DateTimeZone('Europe/Warsaw');
     * // $adjusment = 2;
     */

    // Date format
    $date_format = 'Y-m-d H:i:s';

    // Find start day
    // always the next day
    $dtime = DateTime::createFromFormat($date_format, $launch_date);
    $launch_timestamp = $dtime->getTimestamp();
    $day_of_week = date('N', $launch_timestamp); // 1-Monday, 7-Sunday
    $day_added = 1;
    // if on Friday to Sunday, jump to Monday
    if ($day_of_week >= 5) {
      $day_added += 7 - $day_of_week;
    }

    // Calculate the launch time
    // always at 8 of the latest timezone (USA would be the latest)
    $launch_date_array = explode(' ', $launch_date);
    $time_to_answer = $timezone_adjusment['adjustment']; // will depend on factors
    $time_to_start = 8 + $time_to_answer;
    if ($time_to_start < 10) {
      $time_to_start = '0' . (string)$time_to_start;
    }

    // Assemble launch date/time
    $latest_date = $launch_date_array[0] . ' ' . $time_to_start . ':00:00';
    $latest_date_timestamp = strtotime(date($date_format, strtotime($latest_date)) . ' + '. $day_added .' day');

    // Set timezone difference from latest to client's
    $date_end = new DateTime(date($date_format, $latest_date_timestamp), new DateTimeZone($timezone_adjusment['timezone']));
    $date_end->setTimezone(new DateTimeZone($timezone_client));
    $completion_time = $date_end->format($date_format);

    return $completion_time;
  }

  /**
   * Verify the latest Timezone and Adjustment according to the order of market/country combination
   *
   * @param array $markets - array of markets coming from the front facing form
   * @param array $specialties - array of specialty coming from the front facing form
   *
   * @return array of the timezone and the adjustment
   */
  public function latestTimezoneAndAdjustment($markets, $specialties)
  {
    // check according to data

    // @TODO: maping or data coming soon
    $timezone_and_adjustment = array();
    $timezone_and_adjustment['timezone'] = 'America/New_York';
    $timezone_and_adjustment['adjustment'] = 2;

    return $timezone_and_adjustment;
  }

  /**
   * Validation of the user role
   *
   * @param string $role - user role
   */
  private function validateRole($role) {
    if (!$this->get('security.context')->isGranted($role)) {
      $message = 'Permission denied';
      return new Response($message, 401); // Unauthorised request
    }
  }

  /**
   * A helper function to send confirmation email.
   *
   * @param string  $subject  Email subject
   * @param string  $from     Email address from
   * @param string  $to       Email address to
   * @param string  $type     Confirmation type
   * @param array   $vars     Variables to be passed into twig template
   */
  private function sendConfirmationEmail($subject = 'Hello', $from = 'send@example.com', $to = 'recipient@example.com', $type = 'order_approved.client_copy', $vars = array())
  {
    // 1. Email template with Twig files.
    // 2. Sale’s info template to be re-used everywhere with Twig files.

    $message = \Swift_Message::newInstance()
      ->setSubject($subject)
      ->setFrom($from)
      ->setTo($to)
      ->setBody(
        $this->renderView(
          // src/PSL/ClipperBundle/Resources/views/Emails/confirmation.{order_type}.{user_type}.html.twig
          'PSLClipperBundle:Emails:confirmation.' . $type . '.html.twig',
          array(
            'vars' => $vars,
          ),
          'text/html'
        )
      )
    ;

    $this->get('mailer')->send($message);
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

    // get LS
    $ls = $this->container->get('limesurvey');
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
