<?php
/**
 * Main Clipper Controller
 */

namespace PSL\ClipperBundle\Controller;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\View\View;
use Bigcommerce\Api\Client as Bigcommerce;

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
   * @requestparam(name="name", default="", description="Name.")
   * @requestparam(name="patient_type", default="", description="Patient type.")
   * @requestparam(name="num_participants", default="", description="Number of participants.")
   * @requestparam(name="market", default="", description="Market.")
   * @requestparam(name="specialty", default="", description="Specialty.") 
   * @requestparam(name="timestamp", default="", description="Timestamp.")
   * @requestparam(name="brand", default="", description="Brands.")
   * 
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function postClipperValidationAction(ParamFetcher $paramFetcher)
  {
    // Object to return to remote form
    $returnObject = array();
    
    try {
      // get $_POST values
      $form_data = new stdClass();
      $form_data->loi = 10; // hard coded for now
      $form_data->ir = 10; // hard coded for now
      $form_data->title = $paramFetcher->get('title');
      $form_data->name = $paramFetcher->get('name');
      $form_data->patient_type = 35; //$paramFetcher->get('patient_type');
      $form_data->num_participants = $paramFetcher->get('num_participants');
      $form_data->market = $paramFetcher->get('market');
      $form_data->specialty = $paramFetcher->get('specialty');
      $form_data->timestamp = $paramFetcher->get('timestamp');
      $form_data->brand = explode("|", $paramFetcher->get('brand'));
      
      // Google Spreadsheet validation
      $gsc = New GoogleSpreadsheetController();
      $gsc->setContainer($this->container); 
      $gs_result = $gsc->requestFeasibility($form_data);
      
      // Bigcommerce product creation
      $price = 1234.45;  // $googlesheet_result price// from google sheet
      $bc_product = $this->getBigcommerceProduct($form_data->timestamp, $price);
      
      // Save into the database
      $this->createFirstQProject(serialize($form_data), serialize($gs_result), $bc_product);
      
      // build response
      $returnObject['product']['id'] = $bc_product->id;
      $returnObject['product']['description'] = $gs_result->description;
    } 
    catch (\Doctrine\ORM\ORMException $e) {
      // ORM exception
      
      $returnObject['product'] = FALSE;
      $returnObject['error_message'] = $e->getMessage();
    } 
    catch (\Exception $e) {
      // Return operation specific error
      $returnObject['product'] = FALSE;
      $returnObject['error_message'] = $e->getMessage();
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
  private function getBigcommerceProduct($timestamp, $price)
  {

    if( empty($timestamp) ) {
      throw new Exception('Error while creating Bigcommerce product.');
    }

    // Get parameters
    $parameters_bigcommerce = $this->container->getParameter('bigcommerce');

    // setup bigcommerce
    Bigcommerce::configure(array(
      'username' => $parameters_bigcommerce['api']['username'],
      'store_url' => $parameters_bigcommerce['api']['store_url'],
      'api_key' => $parameters_bigcommerce['api']['api_key']
    ));

    // create new object
    $name = "FirstQ Project {$timestamp}";
    $fields = array(
      'name' => $name,
      'price' => $price,
      'categories' => array(18), // FirstQ
      'type' => 'digital',
      'availability' => 'available',
      'weight' => 0.0,
    );

    $product = Bigcommerce::createProduct($fields);

    if( $product ) {
      return $product;
    }
    else {
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
   * /clipper/limesurvey/{sid}/{slug}
   */
  public function redirectLimeSurveyAction($sid, $slug)
  {
    // Get parameters
    $parameters_ls = $this->container->getParameter('limesurvey');
    
    $destination = strtr($parameters_ls['destination'], array(
      '[SID]' => $sid,
      '[LANG]' => 'en',
      '[SLUG]' => $slug
    ));
    
    return $this->redirect($destination);
  }

}
