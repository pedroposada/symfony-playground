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
use PSL\ClipperBundle\Utils\CountryFWSSO as CountryFWSSO;
use PSL\ClipperBundle\Security\User\FWSSOQuickLoginUser as FWSSOQuickLoginUser;

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
   * Convert an order price
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
   * @requestparam(name="price", default="", description="price of the project.")
   * @requestparam(name="priceLabel", default="", description="price label of the project.")
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function postConvertpriceAction(ParamFetcher $paramFetcher)
  {
    $this->logger = $this->container->get("monolog.logger.clipper");

    $price = (int)str_replace(',', '', $paramFetcher->get('price'));
    $price_label = $paramFetcher->get('priceLabel');
    $request_currency_symbol = mb_substr($price_label, 0, 1);

    $user = $this->get('security.context')->getToken()->getUser();
    $content = $this->getUserObject($user->getUserId());

    $country_id = 0;
    if ($content) {
      $country_id = (isset($content['field_country']['und'][0]['tid'])) ? $content['field_country']['und'][0]['tid'] : '';
    }

    $currency = 'USD';
    $currency_symbol = "$";
    $conversion_rate = 1;

    $currencies =  CountryFWSSO::getCurrencies($country_id);
    if ($currencies) {
      $currency = array_shift($currencies);
    }

    switch ($currency) {
      case 'GBP':
        $currency_symbol = "£";
        $conversion_rate = $this->container->getParameter('currency.conversion.usd-gbp');
        break;

      case 'EUR':
        $currency_symbol = "€";
        $conversion_rate = $this->container->getParameter('currency.conversion.usd-eur');
        break;
    }

    // Conversion only needed when the request currency is different with user currency
    if ($request_currency_symbol != $currency_symbol) {
      $price = $price * $conversion_rate;
      $price_label = $currency_symbol . number_format($price);  
    }
    
    $returnObject = array(
      'price' => $price,
      'priceLabel' => $price_label,
      'currency' => $currency,
      'country' => $country_id
    );

    return new Response($returnObject);
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
   * @requestparam(name="request_counter", default="", description="Number for request of the client.")
   * @requestparam(name="request_timestamp", default="", description="Initial timestamp of the client request.")
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
      $form_data->timestamp = $paramFetcher->get('timestamp');
      $form_data->markets = $paramFetcher->get('market');
      $form_data->specialties = $paramFetcher->get('specialty');
      $form_data->brands = $paramFetcher->get('survey_brand');
      $form_data->attributes = $paramFetcher->get('attribute');
      $form_data->launch_date = $paramFetcher->get('launch_date'); // Y-m-d H:i:s
      $form_data->timezone_client = $paramFetcher->get('timezone_client');
      $form_data->request_counter = $paramFetcher->get('request_counter');
      $form_data->request_timestamp = $paramFetcher->get('request_timestamp');
      $firstq_group_uuid = $paramFetcher->get('firstq_uuid');

      // Security Email Alerts Checking
      $returnObject = array_merge($returnObject, $this->checkOrderRequestLevel($form_data));

      // Google Spreadsheet validation
      $gsc = $this->get('google_spreadsheet');
      $gsc->setupFeasibilitySheet();

      $gs_result_array = array();
      $gs_result_total = 0;
      $num_participants_total = 0;

      foreach ( $form_data->markets as $market_key => $market_value ) {
        foreach ( $form_data->specialties as $specialty_key => $specialty_value ) {
          $form_data_object = new stdClass();
          $form_data_object->loi = 10; // hard coded for now
          $form_data_object->ir = 10; // hard coded for now
          $form_data_object->market = $market_value;
          $form_data_object->specialty = $specialty_value;
          $form_data_object->num_participants = $this->container->get('quota_map')->lookupOne($market_value, $specialty_value);
          $num_participants_total += $form_data_object->num_participants;

          // check feasibility
          $gs_result = $gsc->requestFeasibility($form_data_object);
          // add results
          if( $gs_result ) {
            $gs_result_array[] = $gs_result;
            $gs_result_total += (int)str_replace(',', '', $gs_result->price);
          }
        }
      }
      $form_data->num_participants = $num_participants_total;
      // Always USD for now, convertion will be a new request from front end.
      $gs_result_total_label = '$' . number_format($gs_result_total);
      $form_data->price_total = $gs_result_total_label;

      // calculate estimated time of completion
      $timezone_adjusment = $this->latestTimezoneAndAdjustment($form_data->markets, $form_data->specialties);
      $completion_date = $this->calculateSurveyCompletionTime($form_data->launch_date, $form_data->timezone_client, $timezone_adjusment);

      $form_data->completion_date = $completion_date;

      // Save or update into the database
      $firstq_uuid = $this->createFirstQProject($form_data, $gs_result_array, $firstq_group_uuid);

      // build product response
      $returnObject['product']['price'] = $gs_result_total;
      $returnObject['product']['price_label'] = $gs_result_total_label;
      $returnObject['product']['firstq_uuid'] = $firstq_uuid;
      $returnObject['product']['num_participants'] = $num_participants_total;
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

    // Get user id
    $usr = $this->get('security.context')->getToken()->getUser();
    $user_id = $usr->getUserId();

    if (!empty($user_id)) {
      $firstq_groups = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQGroup')->findByUserId($user_id);
    }

    if (!empty($firstq_groups)) {
      $firstqs_formatted = array();
      foreach ($firstq_groups as $key => $firstq_group) {
        $fqg = $firstq_group->getFormattedFirstQGroup();
        // Get number of ls responses
        $lsresponses = $em->getRepository('PSLClipperBundle:LimeSurveyResponse')->findByFirstqgroup($firstq_group);
        $fqg['current_participants'] = count($lsresponses);
        $firstqs_formatted[] = $fqg;
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
          $content = $this->getUserObject($firstq_group->getUserId());
          $user_info = array();

          if ($content) {
            $first_name = (isset($content['field_firstname']['und'][0]['value'])) ? $content['field_firstname']['und'][0]['value'] : '';
            $last_name = (isset($content['field_lastname']['und'][0]['value'])) ? $content['field_lastname']['und'][0]['value'] : '';
            $company = (isset($content['field_company']['und'][0]['value'])) ? $content['field_company']['und'][0]['value'] : '';
            $phone = (isset($content['field_phone']['und'][0]['value'])) ? $content['field_phone']['und'][0]['value'] : '';
            $company_name = (isset($content['field_company']['und'][0]['value'])) ? $content['field_company']['und'][0]['value'] : '';

            // User info
            $user_info['username'] = $first_name . " " . $last_name;
            $user_info['address'] = $content['mail'];
            $user_info['phone'] = $company . '<br/>' . $phone;
            $user_info['company_name'] = $company_name;
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
   * @requestparam(name="price", default="", description="price of the project.")
   * @requestparam(name="priceLabel", default="", description="price of the project.")
   * @requestparam(name="email", default="", description="Email of the client.")
   * @requestparam(name="method", default="", description="Payment method.")
   * @requestparam(name="project_number", default="", description="Project number for paying with points.")
   * @requestparam(name="vat_number", default="", description="VAT number for paying with credit card.")
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function postOrderProcessAction(ParamFetcher $paramFetcher)
  {
    $this->logger = $this->container->get('monolog.logger.clipper');

    // Get parameters from the POST
    $firstq_group_uuid = $paramFetcher->get('firstq_uuid');

    // payment_method_nonce, not necessary
    $payment_method_nonce = $paramFetcher->get('payment_method_nonce');

    $amount = (int)str_replace(',', '', $paramFetcher->get('price'));
    $amount_label = $paramFetcher->get('priceLabel');

    $method = $paramFetcher->get('method');

    // Get user id
    $usr = $this->get('security.context')->getToken()->getUser();
    $userid = $usr->getUserId();
    $userEmail = $usr->getEmail();

    // Email link components
    // urls
    $frontend_url =  $this->container->getParameter('clipper.frontend.url');
    $backend_url =  $this->container->getParameter('clipper.backend.url');
    // user quick login hash
    $user = new FWSSOQuickLoginUser('', $userEmail, $userEmail, '', array());
    $encKey = $this->container->getParameter('clipper.users.ql_encryptionkey');
    $ql_hash = $user->getQuickLoginHash($encKey);

    // return error if empty
    if (empty($firstq_group_uuid)) {
      $message = 'We were unable to complete your order. Please <a href="/">create your project again</a>.';
      $this->logger->debug('Process order - Invalid request - missing firstq uuid');
      return new Response($message, 400); // invalid request
    }

    $parameters_clipper = $this->container->getParameter('clipper');

    try {

      // Validate if firstq exists and is not processed yet
      $em = $this->getDoctrine()->getManager();
      $firstq_group = $em->getRepository('PSLClipperBundle:FirstQGroup')->find($firstq_group_uuid);
      if (empty($firstq_group) || $firstq_group->getState() != 'ORDER_PENDING') {
        $returnObject['message'] = 'We were unable to complete your order. Please <a href="/">create your project again</a>.';
        $this->logger->debug('Process order - Error - FirstQ uuid is invalid');
        return new Response($returnObject, 400);
      }

      // Update price 
      $form_raw_data = $firstq_group->getFormDataRaw();
      $form_raw_data = json_decode($form_raw_data, TRUE);
      $form_raw_data["price_total"] = $amount_label;


      // Invoice ------------------------------------------------------------------------------------
      if ($method == 'INVOICE') {

        // links
        $client_link = '';
        $admin_link = '';

        if ($this->get('security.context')->isGranted('ROLE_INVOICE_WHITELISTED')) {
          $firstq_group->setState($parameters_clipper['state_codes']['order_complete']);
          $firstq_group->setUserId($userid);
          $returnObject['message'] = 'Order complete. Your payment will be via invoice.';

          // link included in client email
          $client_link['url'] = $frontend_url . '#quick-login&op=dashboard&tab=active&ql_hash=' . $ql_hash;
          $client_link['label'] = 'View your order on dashboard';
        }
        else {
          $firstq_group->setState($parameters_clipper['state_codes']['order_invoice']);
          $firstq_group->setUserId($userid);
          $returnObject['message'] = 'Order pending. The order will be activated after payment.';

          // link included in client email
          $client_link['url'] = $frontend_url . '#quick-login&op=dashboard&tab=pending&ql_hash=' . $ql_hash;
          $client_link['label'] = 'View your order on dashboard';
          // link included in admin email
          $admin_link['url'] = $backend_url;
          $admin_link['label'] = 'Login to view order';
        }

        // Update form data
        $firstq_group->setFormDataRaw($this->getSerializer()->encode($form_raw_data, 'json'));

        $em->persist($firstq_group);
        $em->flush();

        // Send confirmation emails.
        // - Email to client with order/confirmation #, and order details.
        // - Email to FW Finance and others (multi email field) order/confirmation #, and order details.
        $order_state = strtolower($method . '.' . $firstq_group->getState());
        $sales_info = $this->formatOrderInfo($firstq_group);
        $this->sendConfirmationEmail(
          $usr->getEmail(),
          $order_state . '.client_copy', // 'invoice.order_complete.client_copy' or 'invoice.order_invoice.client_copy'
          $sales_info,
          $client_link
        );
        $this->sendConfirmationEmail(
          $this->container->getParameter('confirmation_emails.' . $order_state),
          $order_state . '.admin_copy', // 'invoice.order_complete.admin_copy' or 'invoice.order_invoice.admin_copy'
          $sales_info,
          $admin_link
        );

        return new Response($returnObject, 200);
      }

      // Points ------------------------------------------------------------------------------------
      if ($method == 'POINTS') {

        // Validate the project number
        $project_number = $paramFetcher->get('project_number');
        $project_sub_number = substr($project_number, 0, 3);
        if ($project_sub_number != $this->container->getParameter('payment.points.projectnumber')) {
          $returnObject['message'] = 'Invalid project number';
          return new Response($returnObject, 400); // invalid request
        }

        $form_raw_data["project_number"] = $project_number;

        $firstq_group->setState($parameters_clipper['state_codes']['order_points']);
        $firstq_group->setFormDataRaw($this->getSerializer()->encode($form_raw_data, 'json'));
        $firstq_group->setUserId($userid);
        $em->persist($firstq_group);
        $em->flush();

        $returnObject['message'] = 'Order pending. The order will be activated after payment.';


        // Send confirmation emails.
        // Email to client with order/confirmation #, and order details
        // Email to FW Finance and others (multi email field) order/confirmation #,
        // and order details and include link to Clipper Admin UI
        $order_state = strtolower($method . '.' . $firstq_group->getState());
        $sales_info = $this->formatOrderInfo($firstq_group);
        // link included in client email
        $client_link['url'] = $frontend_url . '#quick-login&op=dashboard&tab=pending&ql_hash=' . $ql_hash;
        $client_link['label'] = 'View your order on dashboard';
        $this->sendConfirmationEmail(
          $usr->getEmail(),
          $order_state . '.client_copy', // 'points.order_points.client_copy'
          $sales_info,
          $client_link
        );
        // link included in admin email
        $admin_link['url'] = $backend_url;
        $admin_link['label'] = 'Login to view order';
        $this->sendConfirmationEmail(
          $this->container->getParameter('confirmation_emails.' . $order_state),
          $order_state . '.admin_copy', // 'points.order_points.admin_copy'
          $sales_info,
          $admin_link
        );

        return new Response($returnObject, 200);
      }

      // Credit ------------------------------------------------------------------------------------
      if ($method == 'CREDIT') {
        if (empty($payment_method_nonce)) {
          $returnObject['message'] = 'We were unable to process your payment. Please try again.';
          $this->logger->debug('Process order - Invalid request - missing parameter payment nonce');
          return new Response($returnObject, 400); // invalid request
        }

        // VAT number
        $vat_number = $paramFetcher->get('vat_number');
        if (!empty($vat_number)) {
          $form_raw_data['vat_number'] = $vat_number;
        }

        // create the charge on Braintree's servers
        // this will charge the user's card
        $parameters_clipper = $this->container->getParameter('clipper');

        $this->initBrainTree();

        $sale_params = array(
          'amount' => $amount,
          'paymentMethodNonce' => $payment_method_nonce
        );

        $result = \Braintree_Transaction::sale($sale_params);

        // Check that it was paid:
        if ($result->success == TRUE) {

          // Check that it was paid:
          // change status to order complete and return ok for redirect
          $firstq_group->setState($parameters_clipper['state_codes']['order_complete']);
          $firstq_group->setFormDataRaw($this->getSerializer()->encode($form_raw_data, 'json'));
          $firstq_group->setOrderId($result->transaction->id);
          $firstq_group->setUserId($userid);
          $em->persist($firstq_group);
          $em->flush();

          $returnObject['fquuid'] = $firstq_group_uuid;
          $returnObject['message'] = 'Thank you for your payment.';

          // Send confirmation emails.
          // Email to client with order/confirmation #, and order details
          // Email to FW Finance and others (multi email field)
          $order_state = strtolower($method . '.' . $firstq_group->getState());
          $sales_info = $this->formatOrderInfo($firstq_group);
          // link included in client email
          $client_link['url'] = $frontend_url . '#quick-login&op=dashboard&tab=active&ql_hash=' . $ql_hash;
          $client_link['label'] = 'View your order on dashboard';
          $this->sendConfirmationEmail(
            $usr->getEmail(),
            $order_state . '.client_copy', // 'credit.order_complete.client_copy'
            $sales_info,
            $client_link
          );
          $admin_link = '';
          $this->sendConfirmationEmail(
            $this->container->getParameter('confirmation_emails.' . $order_state),
            $order_state . '.admin_copy', // 'credit.order_complete.admin_copy'
            $sales_info,
            $admin_link
          );

          return new Response($returnObject, 200);
        }
        else {
          // failed

          // @see https://developers.braintreepayments.com/reference/response/transaction/php#result-object

          // We will check if there's any errors
          $error_message = '';
          $error_code = '';
          foreach($result->errors->deepAll() AS $error) {
            $error_message .= $error->message . "\n";
            // we need only 1 error code, this is for frontend to trigger error message.
            $error_code = $error->code;
          }

          // No errors, but it could be from processor
          if (empty($error_code)) {
            if (isset($result->transaction->processorResponseCode)) {
              $error_code = $result->transaction->processorResponseCode;
              $error_message = $result->transaction->processorResponseText;
            }
          }
          $this->logger->debug("Payment System Error : " . var_export($result->errors, true));
          $returnObject['message'] = $error_message;
          return new Response($returnObject, 400);
        }
      }
    }
    catch (\Exception $e) {
      // Something messed up
      $this->logger->debug("Process order - exception: {$e}");
      $returnObject['message'] = 'We were unable to process your payment. Please try again.';
      return new Response($returnObject, 400); // Error
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

    if (empty($firstq_group) || ($firstq_group->getState() != 'ORDER_INVOICE' && $firstq_group->getState() != 'ORDER_POINTS')) {
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
      $this->logger->debug("Process order admin - exception: {$e}");
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
    $this->initBrainTree();

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
   * Get BrainTree Object
   */
  private function initBrainTree($currency = '')
  {
    // We will currency from user country if no currency is defined.
    if (empty($currency)) {
      $usr = $this->get('security.context')->getToken()->getUser();
      $userid = $usr->getUserId();

      // User info retrieval from the FW SSO
      $userObject = $this->getUserObject($userid);
      $country_id = 0;
      if ($userObject) {
        $country_id = (isset($userObject['field_country']['und'][0]['tid'])) ? $userObject['field_country']['und'][0]['tid'] : '';
      }

      $currency =  CountryFWSSO::getCurrencies($country_id);
      if ($currency) {
        $currency = array_shift($currency);
      } else {
        $currency = 'USD';
      }
    }

    switch (strtoupper($currency)) {
      case 'EUR':
        $region_code = 'eu';
        break;

      case 'USD':
        $region_code = 'us';
        break;

      case 'GBP':
      default:
        $region_code = 'uk';
        break;
    }

    // Set Braintree configuration.
    \Braintree_Configuration::environment($this->container->getParameter('braintree_' . $region_code . '.environment'));
    \Braintree_Configuration::merchantId($this->container->getParameter('braintree_' . $region_code . '.merchant_id'));
    \Braintree_Configuration::publicKey($this->container->getParameter('braintree_' . $region_code . '.public_key'));
    \Braintree_Configuration::privateKey($this->container->getParameter('braintree_' . $region_code . '.private_key'));
  }


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
        $fqps = $em->getRepository('PSLClipperBundle:FirstQProject')->findByFirstqgroup($firstq_group->getId());
        foreach ($fqps as $fqp) {
          $em->remove($fqp);
        }
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
   * Check order request level
   *
   * @param object $form_data - form data
   */
  private function checkOrderRequestLevel($form_data) {

    $returnObject = array();

    // get config
    $counter = $this->container->getParameter('security_alerts.order_request.counter');
    $timeframe = $this->container->getParameter('security_alerts.order_request.timeframe');

    $request_counter = isset($form_data->request_counter) ? $form_data->request_counter : 0;
    $request_timestamp = isset($form_data->request_timestamp) ? $form_data->request_timestamp : 0;

    if ($request_timestamp == 0) {
      $request_timestamp = time();
    }

    $returnObject['request_timestamp'] = $request_timestamp;

    // check if the request is within timeframe?
    $timestamp = time();
    $returnObject['reset_counter'] = FALSE;
    if ($timestamp - $request_timestamp > $timeframe) {
      // longer than the timeframe, reset counter
      $returnObject['reset_counter'] = TRUE;
    } else {
      // within the timeframe
      // check if counter is hit or not
      if ($request_counter >= $counter) {
        $this->sendSecurityEmail();
        $returnObject['reset_counter'] = TRUE;
      }
    }
    return $returnObject;
  }

  function sendSecurityEmail() {

    // Check if user is logged in
    $user = $this->get('security.context')->getToken()->getUser();

    $user_log = ''; // message for email and log

    $user_info = array();
    $user_info['name'] = '';
    $user_info['company_name'] = '';
    $user_info['email'] = '';
    $user_info['ip'] = $this->container->get('request')->getClientIp();

    if (!is_string($user) || $user != 'anon.') {
      // if logged in, get data
      $userid = $user->getUserId();
      $userEmail = $user->getEmail();

      // User info retrieval from the FW SSO
      $content = $this->getUserObject($userid);

      if ($content) {
        $first_name = (isset($content['field_firstname']['und'][0]['value'])) ? $content['field_firstname']['und'][0]['value'] : '';
        $last_name = (isset($content['field_lastname']['und'][0]['value'])) ? $content['field_lastname']['und'][0]['value'] : '';
        $company_name = (isset($content['field_company']['und'][0]['value'])) ? $content['field_company']['und'][0]['value'] : '';
        $user_info['name'] = $first_name . ' ' . $last_name;
        $user_info['company_name'] = $company_name;
      }
      $user_info['email'] = $userEmail;
      $user_log .= 'Name: ' . $user_info['name'] . ' Company: ' . $user_info['company_name'] . ' Email: ' . $user_info['email'];
    }
    $this->logger = $this->container->get('monolog.logger.clipper');
    $user_log .= ' IP:' . $user_info['ip'];
    $this->logger->info('Email sent for high volume of Google sheet requests - ' . $user_log);

    $subject = "Security Alerts - abnormal order request level.";
    $from = $this->container->getParameter('security_alerts.email_from');
    $to = $this->container->getParameter('security_alerts.email_to');

    $message = \Swift_Message::newInstance()
      ->setContentType('text/html')
      ->setSubject($subject)
      ->setFrom($from)
      ->setTo($to)
      ->setBody(
        $this->renderView(
          'PSLClipperBundle:Emails:security.alerts.html.twig',
          array('user_log' => $user_log),
          'text/html'
        )
      )
    ;

    $this->get('mailer')->send($message);
  }

  /**
   * A helper function to send confirmation email.
   *
   * @param mixed   $to           String of emails separated by comma or array of email addresses
   * @param string  $type         Confirmation type, format as
   * @param array   $sales_info   Passing variables to salesinfo twig template
   */
  private function sendConfirmationEmail($to = array('recipient@example.com'), $type = '', $sales_info = array(), $link = '')
  {
    // @TODO: Update the text/content when it's ready.
    $subject = '';
    $no_reply_email = $this->container->getParameter('clipper.no_reply_email');
    $website_name = $this->container->getParameter('clipper.website_name');
    $from = array($no_reply_email => $website_name);

    // Become smart to break emails into array.
    if (is_string($to)) {
      if (strpos($to, ',')) {
        $to = explode(',', $to);
      }
      else {
        $to = array($to);
      }
    }

    switch ($type) {
      // Invoice pending ------------------------------
      case 'invoice.order_invoice.client_copy':
        $subject = 'Your order is pending.';
        break;
      case 'invoice.order_invoice.admin_copy':
        $subject = 'A pending order is created.';
        break;

      // Invoice complete ------------------------------
      case 'invoice.order_complete.client_copy':
        $subject = 'Your order is ready.';
        break;
      case 'invoice.order_complete.admin_copy':
        $subject = 'An order is ready.';
        break;

      // Points pending ------------------------------
      case 'points.order_points.client_copy':
        $subject = 'Your order is pending.';
        break;
      case 'points.order_points.admin_copy':
        $subject = 'A pending order is created.';
        break;

      // Credit card ------------------------------
      case 'credit.order_complete.client_copy':
        $subject = 'Your order is ready.';
        break;
      case 'credit.order_complete.admin_copy':
        $subject = 'An order is ready.';
        break;

      // Project completed ------------------------------
      // @TODO: project complete
    }

    $message = \Swift_Message::newInstance()
      ->setContentType('text/html')
      ->setSubject($subject)
      ->setFrom($from)
      ->setTo($to)
      ->setBody(
        $this->renderView(
          // src/PSL/ClipperBundle/Resources/views/Emails/confirmation.{order_type}.{user_type}.html.twig
          'PSLClipperBundle:Emails:confirmation.' . $type . '.html.twig',
          array(
            'sales_info' => $sales_info,
            'link' => $link,
          ),
          'text/html'
        )
      )
    ;

    $this->get('mailer')->send($message);
  }

  /**
   * format the order info for the emails
   */
  private function formatOrderInfo($firstq_group) {

    $sale_info = array();

    $firstq_formatted = $firstq_group->getFormattedFirstQGroup();

    // User info retrieval from the FW SSO
    $content = $this->getUserObject($firstq_group->getUserId());
    $user_info = array();

    if ($content) {
      $first_name = (isset($content['field_firstname']['und'][0]['value'])) ? $content['field_firstname']['und'][0]['value'] : '';
      $last_name = (isset($content['field_lastname']['und'][0]['value'])) ? $content['field_lastname']['und'][0]['value'] : '';
      $company_name = (isset($content['field_company']['und'][0]['value'])) ? $content['field_company']['und'][0]['value'] : '';

      // User info
      $user_info['name'] = $first_name . " " . $last_name;
      $user_info['company_name'] = $company_name;
    }

    $markets = '';
    foreach ($firstq_formatted['markets'] as $mkey => $mvalue) {
      $markets .= $mvalue . ', ';
    }
    $markets = rtrim($markets, ', ');
    $specialties = '';
    foreach ($firstq_formatted['specialties'] as $skey => $svalue) {
      $specialties .= $svalue . ', ';
    }
    $specialties = rtrim($specialties, ', ');

    $sale_info['user_name'] = $user_info['name'];
    $sale_info['company'] = $user_info['company_name'];
    $sale_info['title'] = $firstq_formatted['title'];
    $sale_info['launch_date'] = $firstq_formatted['launch_date'];
    $sale_info['markets'] = $markets;
    $sale_info['specialties'] = $specialties;
    $sale_info['price'] = $firstq_formatted['price'];

    if (!empty($firstq_formatted['project_number'])) {
      $sale_info['project_number'] = $firstq_formatted['project_number'];
    }

    if (!empty($firstq_formatted['vat_number'])) {
      $sale_info['vat_number'] = $firstq_formatted['vat_number'];
    }


    return $sale_info;
  }

  /**
   * Returns the user object from the FW SSO
   *
   * @param: int $user_id - FW SSO user id
   */
  private function getUserObject($user_id) {
    // User info retrieval from the FW SSO
    $settings['fwsso_baseurl'] = $this->container->getParameter('fwsso_api.url');
    $settings['fwsso_app_token'] = $this->container->getParameter('fwsso_api.app_token');

    $fwsso_ws = $this->container->get('fw_sso_webservice');
    $fwsso_ws->configure($settings);
    $response = $fwsso_ws->getUser(array('uid' => $user_id));

    if ($response->isOk()) {
      $content = @json_decode($response->getContent(), TRUE);
      if (json_last_error() != JSON_ERROR_NONE) {
        throw new Exception('Get User object - JSON decode error: ' . json_last_error());
      }
      else {
        return $content;
      }
    }
    else {
      return FALSE;
    }
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
