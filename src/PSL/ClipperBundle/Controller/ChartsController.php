<?php
/**
 * Main Clipper Controller
 */

namespace PSL\ClipperBundle\Controller;

use \stdClass;
use \Exception;
use \DateTime;
use \DateTimeZone;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
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
  private static $js_charttype_postfix = '_Chart';
  
  /**
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
    
    foreach ($map['machine_names'] as $machine_name) {
      try {
        $placeholders = array(
          'dataTable' => $assembler->getChartDataTable($order_id, $machine_name, $survey_type),
          'chartDivId' => uniqid(),
        );
        $charts->add($this->container->get('twig')->render("PSLClipperBundle:Charts:{$machine_name}.html.twig", $placeholders));
      } catch (Exception $e) {
        // Do something, maybe?
      }
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
   *    {"name"="chartmachinename", "dataType"="string"},
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
    $content = null;
    $code = 200;
    
    try {
      $order_id = $request->request->get('order_id');
      $chartmachinename = $request->request->get('chartmachinename', '');
      $filters = array(
        'country'   => $request->request->get('country', ''),
        'region'    => $request->request->get('region', ''),
        'specialty' => $request->request->get('specialty', ''),
      );
      $charts = new ArrayCollection();
      $em = $this->container->get('doctrine')->getManager();
      $fqg = $em->getRepository('PSLClipperBundle:FirstQGroup')->find($order_id);
      if (!$fqg) {
        throw new Exception("FQG with id [{$order_id}] not found");
      }
      
      $survey_type = current($fqg->getFormDataByField('survey_type'));
      $map = $this->container->get('survey_chart_map')->map($survey_type);
      $assembler = $this->container->get('chart_assembler');
      
      foreach ($map['machine_names'] as $index => $machine_name) {
        $drilldown = $chartmachinename == $machine_name ? $filters : array();
        $chEvent = $assembler->getChartEvent($order_id, $machine_name, $survey_type, $drilldown);
        $chart = array(
          'chartmachinename' => $machine_name,
          'charttype'        => $machine_name . self::$js_charttype_postfix,
          'drilldown'        => $chEvent->getDrillDown(),
          'filter'           => $chEvent->getFilters(),
          'countTotal'       => $chEvent->getCountTotal(),
          'countFiltered'    => $chEvent->getCountFiltered(),
          'datatable'        => $chEvent->getDataTable(),
          'header'           => 'Maecenas faucibus mollis interdum.',
          'footer'           => 'Cras mattis consectetur purus sit amet fermentum.',
          'titleLong'        => $chEvent->getTitleLong();
        );
        $charts->add($chart);
      }
      $first = $charts->first();
      $content['meta'] = array(
        "projectTitle": current($fqg->getFormDataByField('name_full')), 
        "totalResponses": $first['countTotal'],
        "quota":10,
        "finalReportReady": "2015-10-13 9:00pm EST",
        "introduction":"Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.",
        "introImage": "/images/nps-calculation.png",
        "conclusion":"Sed posuere consectetur est at lobortis."
      );  
      $content['charts'] = $charts;
    }
    catch(Exception $e) {
      $content = "{$e->getMessage()} - File [{$e->getFile()}] - Line [{$e->getLine()}]";
      $code = 204;
    }
    
    return new Response($content, $code);
  }
}
