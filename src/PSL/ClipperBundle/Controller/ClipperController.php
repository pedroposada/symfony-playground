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

use PSL\ClipperBundle\Entity\Repository\FirstQProjectRepository;


/**
 * Rest controller for Clipper
 */
class ClipperController extends FOSRestController
{
  
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