<?php
/**
 * Main Clipper Controller
 */

namespace PSL\ClipperBundle\Controller;

use \stdClass as stdClass;
use \Exception as Exception;
use \DateTime as DateTime;
use \DateTimeZone as DateTimeZone;
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
use Doctrine\Common\Collections\ArrayCollection;

// custom


/**
 * Rest Controller for Clipper
 */
class ChartsController extends FOSRestController
{
  /**
   * Get google chart DataTable
   * /clipper/charts
   *
   * @param ParamFetcher $paramFetcher
   *
   * @QueryParam(name="order_id", default="(empty)", description="FirstQGroup UUID")
   * @QueryParam(name="params", default="(empty)", description="Array of optional filters")
   */
  public function chartsAction(ParamFetcher $paramFetcher)
  {
    $charts = new ArrayCollection();
    $order_id = $paramFetcher->get('order_id');
    // TODO: add 'survey_type' property to form_data_raw
    // $em = $this->getDoctrine()->getManager();
    // $survey_type = $em->getRepository('PSLClipperBundle:FirstQGroup')->find($order_id)->getFormDataByField('survey_type');
    $survey_type = 'nps_plus';
    $map = $this->container->get('survey_chart_map')->map($survey_type);
    
    foreach ($map['chart_types'] as $chart_type) {
      $dataTable = $this->container
        ->get('chart_assembler')
        ->getChartDataTable($order_id, $chart_type, $survey_type);
      $placeholders = array(
        'dataTable' => $dataTable,
        'chartDivId' => uniqid(),
      );
      $charts->add($this->container->get('twig')->render("PSLClipperBundle:Charts:{$chart_type}.html.twig", $placeholders));
    }
    
    return $this->render("PSLClipperBundle:Charts:charts.html.twig", array('charts' => $charts));
  }
}
