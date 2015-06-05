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
use PSL\ClipperBundle\Entity;

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
     * @requestparam(name="country", default="", description="country.")
     * @requestparam(name="specialty", default="", description="specialty.") 
     * @requestparam(name="timestamp", default="", description="timestamp.")
     * 
     * @return \Symfony\Component\BrowserKit\Response
     */
    public function postClipperValidationAction(ParamFetcher $paramFetcher)
    {
        // Object to return to remote form
        $returnObject = array();
        
        try {
            // get $_POST values
            $form_data = new \stdClass();
            
            $form_data->loi = $paramFetcher->get('loi');
            $form_data->ir = $paramFetcher->get('ir');
            $form_data->country = $paramFetcher->get('country');
            $form_data->specialty = $paramFetcher->get('specialty');
            $form_data->timestamp = $paramFetcher->get('timestamp'); 
            
            // Google Spreadsheet validation
            $gsc = New GoogleSpreadsheetController();
            $gsc->setContainer($this->container); 
            $gs_result = $gsc->requestFeasibility($form_data);
            
            // Bigcommerce product creation
            $price = 1234.45;  // $googlesheet_result price// from google sheet
            $bc_product = $this->getBigcommerceProduct($form_data->timestamp, $price);
            
            // Save into the database
            // $this->createFirstQProject(serialize($form_data), serialize($gs_result), $product);
            
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
    private function getBigcommerceProduct($timestamp, $price) {
        
        if (empty($timestamp)) {
            throw new \Exception('Error while creating Bigcommerce product.');
        }
        
        // Get parameters
        $parameters_bigcommerce = $this->container->getParameter('bigcommerce');
        
        // setup bigcommerce
        Bigcommerce::configure(array(
            'username'  => $parameters_bigcommerce['api']['username'],
            'store_url' => $parameters_bigcommerce['api']['store_url'],
            'api_key'   => $parameters_bigcommerce['api']['api_key']
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
        
        if ($product) {
            return $product;
        }
        else {
            throw new \Exception('Error while creating Bigcommerce product.');
        }
    }
    
    /**
     * Saves a new FirstQProject or update an existing one
     *
     * @param string $form_data_serialized - product created  
     * @param string $gs_result_serialized - product created  
     * @param mixed $price - price returned by the Google Spreadsheet
     */
    private function createFirstQProject($form_data_serialized, $gs_result_serialized, $product) {
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
        $firstq_project->setBcProductId($product->id); // from the item creation from Bigcommerce
        $firstq_project->setFormDataRaw($form_data_serialized); // from form
        $firstq_project->setSheetDataRaw($gs_result_serialized); // from GoogleSheet
        $firstq_project->setState($parameters_clipper['state_codes']['bigcommerce_pending']);
        
        $em = $this->getDoctrine()->getManager();
        
        $em->persist($firstq_project);
        $em->flush();
    }
}
