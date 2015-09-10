<?php
/**
 * Main Clipper Controller
 */

namespace PSL\ClipperBundle\Controller;

use \stdClass;
use \Exception;
use \DateTime;
use \DateTimeZone;
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
   * TODO: to be removed, this end-point will be replaced with chartsReactAction
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
    $em = $this->container->get('doctrine')->getManager();
    $survey_type = $em->getRepository('PSLClipperBundle:FirstQGroup')->find($order_id)->getFormDataByField('survey_type');
    $survey_type = reset($survey_type);
    $map = $this->container->get('survey_chart_map')->map($survey_type);
    $assembler = $this->container->get('chart_assembler');
    
    foreach ($map as $chart_type => $val) {
      $placeholders = array(
        'dataTable' => $assembler->getChartDataTable($order_id, $chart_type, $survey_type),
        'chartDivId' => uniqid(),
      );
      $charts->add($this->container->get('twig')->render("PSLClipperBundle:Charts:{$chart_type}.html.twig", $placeholders));
    }
    
    return $this->render("PSLClipperBundle:Charts:charts.html.twig", array('charts' => $charts));
  }

  /**
   * Present google charts
   * /clipper/charts/react
   *
   * @param ParamFetcher $paramFetcher
   *
   * @QueryParam(name="order_id", default="(empty)", description="FirstQGroup UUID")
   */
  public function chartsReactAction(ParamFetcher $paramFetcher)
  {
    $order_id = $paramFetcher->get('order_id');
    
    return $this->render("PSLClipperBundle:Charts:charts-react.html.twig", array(
      'order_id' => $order_id,
    ));
  }
  
  /**
   * @ApiDoc(
   *   resource=true,
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     204 = "No Content for the parameters passed"
   *   },
   *  description="json to render all charts",
   *  filters={
   *    {"name"="order_id", "dataType"="string"},
   *    {"name"="country", "dataType"="string"},
   *    {"name"="region", "dataType"="string"},
   *    {"name"="specialty", "dataType"="string"},
   *  }
   * )
   * 
   * /clipper/charts/reacts
   *
   * @param Request $request the request object
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function postReactAction(Request $request)
  {
    try {
      $order_id = $request->request->get('order_id');
      $drilldown['country'] = $request->request->get('country', null);
      $drilldown['region'] = $request->request->get('region', null);
      $drilldown['specialty'] = $request->request->get('specialty', null);
      
      $charts = new ArrayCollection();
      $em = $this->container->get('doctrine')->getManager();
      $survey_type = $em->getRepository('PSLClipperBundle:FirstQGroup')->find($order_id)->getFormDataByField('survey_type');
      $survey_type = reset($survey_type);
      $map = $this->container->get('survey_chart_map')->map($survey_type);
      $assembler = $this->container->get('chart_assembler');
      
      // TODO: uncomment foreach loop
      // foreach ($map as $chart_type => $val) {
        $chEvent = $assembler->getChartEvent($order_id, 'net_promoters', $survey_type, $drilldown);
        $chart = array(
          'datatable' => $chEvent->getDataTable(),
          // TODO: replace with $chEvent->getChartType()
          'charttype' => "BarChart",
          // TODO: replace with $chEvent->getOptions()
          'options' => array(
            'isStacked' => 'percent',
            'legend' => array(
              'position' => 'top',
              'maxLines' => 3,
            ),
          ),
          // TODO: replace with $chEvent->getDrilldown()
          'drilldown' => array(
            'countries' => array('USA', 'Canada'),
            'specialties' => array('Oncology', 'Diabetes'),
            'regions' => array('Europe', 'USA'),
          ),
        );
        $charts->add($chart);
      // }
      
      $response = new Response($charts);
    }
    catch(Exception $e) {
      $response = new Response($e->getMessage(), 204);
    }
    
    return $response;
  }
}
