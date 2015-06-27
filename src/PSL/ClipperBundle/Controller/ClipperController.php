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
use Bigcommerce\Api\Client as Bigcommerce;

// custom
use PSL\ClipperBundle\Utils\LimeSurvey as LimeSurvey;
use PSL\ClipperBundle\Controller\GoogleSpreadsheetController;
use PSL\ClipperBundle\Entity\Repository\FirstQProjectRepository;
use PSL\ClipperBundle\Entity\FirstQProject;

use \stdClass as stdClass;
use \Exception as Exception;

/**
 * Rest Controller for Clipper
 *
 * @TODO:
 * - monolog of errors and states
 *
 *
 */
class ClipperController extends FOSRestController
{
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
      $expression = '//field[starts-with(translate(., "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "'. $input .'")][position() <= 20]';
      
      $results = $xpath->query($expression);
      foreach ($results as $node) {
        $result[] = $node->nodeValue;
      }
    }
    
    
    return new Response($result);
  }

  /**
   * Validate a FristQ request
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
   * @requestparam(name="loi", default="", description="LOI.")
   * @requestparam(name="ir", default="", description="IR.")
   * @requestparam(name="title", default="", description="Title.")
   * @requestparam(name="name", default="", description="Name of the firstq
   * project.")
   * @requestparam(name="name_full", default="", description="Full name of the
   * firstq project.")
   * @requestparam(name="patient_type", default="", description="Patient type.")
   * @requestparam(name="num_participants", default="", description="Number of
   * participants.")
   * @requestparam(name="market", default="", description="Market.", array=true)
   * @requestparam(name="specialty", default="", description="Specialty.",
   * array=true)
   * @requestparam(name="timestamp", default="", description="Timestamp.")
   * @requestparam(name="survey_brand", default="", description="Brands.",
   * array=true)
   * @requestparam(name="brand", default="", description="Brands.")
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function postClipperValidationAction(ParamFetcher $paramFetcher)
  {

    $this->logger = $this->container->get('monolog.logger.clipper');

    // Object to return to remote form
    $returnObject = array();

    try {
      // get $_POST values
      $form_data = new stdClass();
      $form_data->loi = 10;
      // hard coded for now
      $form_data->ir = 10;
      // hard coded for now
      $form_data->title = $paramFetcher->get('title');
      $form_data->name = $paramFetcher->get('name');
      $form_data->name_full = $paramFetcher->get('name_full');
      $form_data->patient_type = $paramFetcher->get('patient_type');
      $form_data->num_participants = 35;
      // hard coded for now //$paramFetcher->get('num_participants');
      $form_data->timestamp = $paramFetcher->get('timestamp');
      $form_data->markets = $paramFetcher->get('market');
      $form_data->specialties = $paramFetcher->get('specialty');
      $form_data->brand = $paramFetcher->get('survey_brand');

      // Google Spreadsheet validation
      $gsc = New GoogleSpreadsheetController();
      $gsc->setContainer($this->container);

      $gs_result_array = array();
      $gs_result_total = 0;

      foreach ( $form_data->markets as $market_key => $market_value ) {
        foreach ( $form_data->specialties as $specialty_key => $specialty_value ) {
          $form_data_object = new stdClass();
          $form_data_object->loi = 10;
          // hard coded for now
          $form_data_object->ir = 10;
          // hard coded for now
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

      // Description of the product
      // @TODO: use TWIG template to format output
      $description = '<p>Market: ' . implode(', ', $form_data->markets) . '</p>';
      $description .= '<p>Specialty: ' . implode(', ', $form_data->specialties) . '</p>';
      $description .= '<p>Quota: ' . $form_data->num_participants . '</p>';
      $description .= '<p>Brands: ' . implode(', ', $form_data->brand) . '</p>';
      // $description .= '<h4>Total price: $' . number_format($gs_result_total,
      // 2, ',', ',') . '</h4>';
      // @TODO: change hardcoded price
      $description .= '<h4>Total price: $' . number_format(4995, 2, ',', ',') . '</h4>';

      // Bigcommerce product creation
      $bc_product = $this->getBigcommerceProduct($form_data, $gs_result_total, $description);
      // Save into the database
      $this->createFirstQProject(serialize($form_data), serialize($gs_result_array), $bc_product);

      // build response
      $returnObject['product']['id'] = $bc_product->id;
      $returnObject['product']['name'] = 'FirstQ ' . $form_data->name_full;
      $returnObject['product']['description'] = $description;
    }
    catch (\Doctrine\ORM\ORMException $e) {
      // ORM exception

      $returnObject['product'] = FALSE;
      $returnObject['error_message'] = $e->getMessage();
      $this->logger->debug("Doctrine exception: {$e}");
    }
    catch (\Exception $e) {
      // Return operation specific error
      $returnObject['product'] = FALSE;
      $returnObject['error_message'] = $e->getMessage();
      $this->logger->debug("General exception: {$e}");
    }

    return new Response($returnObject);
  }

  /**
   * Retrieve a clipper.
   *
   * @ApiDoc(
   *   resource=true,
   *   statusCodes = {
   *     200 = "Returned when successful",
   *   }
   * )
   *
   * @param Request $request the request object
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function getClipperGetAction(Request $request)
  {
    $em = $this->getDoctrine()->getEntityManager();

    $fq = $em->getRepository('PSLClipperBundle:FirstQProject')->getLatestFQs();

    return new Response($fq);
  }

  /**
   * Create a Bigcommerce Product
   *
   * @param integer $timestamp - timestamp of the request
   * @param float $price - price returned by the Google Spreadsheet
   *
   * @return mixed product
   */
  private function getBigcommerceProduct($form_data, $price, $description)
  {

    if( empty($form_data->timestamp) ) {
      throw new Exception('Error while creating Bigcommerce product. Missing parameter.');
    }

    // Get parameters
    $parameters_bigcommerce = $this->container->getParameter('bigcommerce');

    // setup bigcommerce
    Bigcommerce::failOnError();
    Bigcommerce::configure(array(
      'username' => $parameters_bigcommerce['api']['username'],
      'store_url' => $parameters_bigcommerce['api']['store_url'],
      'api_key' => $parameters_bigcommerce['api']['api_key']
    ));

    // create new object
    $name = "FirstQ {$form_data->name_full} - {$form_data->timestamp}";
    $fields = array(
      'name' => $name,
      'price' => $price,
      'categories' => array($parameters_bigcommerce['category_code_firstq']), //
      // FirstQ
      'description' => $description,
      'type' => 'digital',
      'availability' => 'available',
      'weight' => 0.0,
    );

    $product = Bigcommerce::createProduct($fields);

    if( $product ) {
      $this->logger->info("Bigcommerce project {$product->id} was created.");

      return $product;
    }
    else {
      $last_error = Bigcommerce::getLastError();
      $this->logger->error(print_r($last_error, 1));
      $this->logger->debug(print_r($produc,1));
      throw new Exception('Error while creating Bigcommerce product.');
    }
  }

  /**
   * Saves a new FirstQProject or update an existing one
   *
   * @param string $form_data_serialized - product created
   * @param string $gs_result_serialized - product created
   * @param mixed $price - price returned by the Google Spreadsheet
   */
  private function createFirstQProject($form_data_serialized, $gs_result_serialized, $product)
  {
    // Get parameters
    $parameters_clipper = $this->container->getParameter('clipper');

    // Check if object exists already
    // Return and update if exists
    // Create if new

    // Create FirstQProject entity
    $firstq_project = new FirstQProject();
    // $firstq_project->setGuid(); // get from the item creation from Bigcommerce
    // $firstq_project->setBcClientId(); // not used for this step
    // $firstq_project->setBcClientName(); // not used for now
    $firstq_project->setBcProductId($product->id);
    // from the item creation from Bigcommerce
    $firstq_project->setFormDataRaw($form_data_serialized);
    // from form
    $firstq_project->setSheetDataRaw($gs_result_serialized);
    // from GoogleSheet
    $firstq_project->setState($parameters_clipper['state_codes']['bigcommerce_pending']);

    $em = $this->getDoctrine()->getManager();

    $em->persist($firstq_project);
    $em->flush();
  }

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
   * Simple debug output
   * /clipper/autocomplete
   *
   */
  public function autocompleteAction()
  {
    $response = $this->render('PSLClipperBundle:Clipper:autocomplete.html.twig');

    return $response;
  }

}
