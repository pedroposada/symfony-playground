<?php

namespace PSL\ClipperBundle\Controller;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\BrowserKit\Response;
use Bigcommerce\Api\Client as Bigcommerce;

use PSL\ClipperBundle\Controller\GoogleSpreadsheetController;
use PSL\ClipperBundle\Entity\Repository\FirstQProjectRepository;


/**
 * Rest controller for Clipper
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
     * @param int $loi          - LOI number
     * @param int $ir           - IR number
     * @param string $country   - Country name
     * @param string $specialty - Specialty name
     *
     * @return the GoogleSheet
     */
    // public function getClipperValidationGetAction(Request $request)
    public function getClipperValidationGetAction(Request $request)
    {
        $request = $this->getRequest();
        
        $loi = $request->query->get('loi');
        $ir = $request->query->get('ir');
        $country = $request->query->get('country');
        $specialty = $request->query->get('specialty');
        
        $gsc = New GoogleSpreadsheetController();
        $gsc->setContainer($this->container); 
        $return = $gsc->requestFeasibility($loi, $ir, $country, $specialty);
        
        return $return;
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
  
}