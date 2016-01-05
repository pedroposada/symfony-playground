<?php
/**
 * Main Clipper Charts Controller
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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

use PSL\ClipperBundle\ClipperEvents;
use PSL\ClipperBundle\Event\ChartEvent;


// custom


/**
 * Rest Controller for Clipper
 */
class ChartsController extends FOSRestController
{
  private $survey_type;

  /**
   * This endpoint renders charts in its template.
   * /clipper/charts
   *
   * @param ParamFetcher $paramFetcher
   *
   * @deprecated This endpoint no longer viable on new presentation layer. 
   * - @see chartsReactAction() 
   * - @uses postReactAction()
   * - @uses \ClipperChartsService
   * @TODO: Remove this
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
   *    {"name"="brand", "dataType"="string"},
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
      $filters = array(
        'country'   => $request->request->get('country', ''),
        'region'    => $request->request->get('region', ''),
        'specialty' => $request->request->get('specialty', ''),
        'brand'     => $request->request->get('brand', ''),
      );
      $chartmachinename = $request->request->get('chartmachinename', '');
      // getting chart
      $content = $this->container->get('chart_helper')->getDataStructure($order_id, $filters, $chartmachinename);
    }
    catch(Exception $e) {
      $content = "{$e->getMessage()} - File [{$e->getFile()}] - Line [{$e->getLine()}]";
      $code = 204;
    }

    return new Response($content, $code);
  }

  /**
   * Preview the PDF to be downloaded
   * clipper/charts/pdfpreview
   *
   * @param ParamFetcher $paramFetcher
   *
   * @QueryParam(name="order_id", default="(empty)", description="collection of objects to generate multipage pdf")
   * @QueryParam(name="page", default="1", description="current page to render")
   * @QueryParam(name="drilldown", default="", description="drilldown options")
   * 
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function previewPdfAction(ParamFetcher $paramFetcher)
  {
    $content = null;
    $code = 200;

    try {
      $order_id = $paramFetcher->get('order_id');
      $page = $paramFetcher->get('page');
      $drilldown = $paramFetcher->get('drilldown');
      $filters = array();

      // Parse drilldown
      $f_arr = explode(',', $drilldown);
      foreach ($f_arr as $value) {
        $f_tmp = explode(':', $value);
        if (count($f_tmp) > 1) {
          $filters[$f_tmp[0]] = $f_tmp[1];
        } else if (count($f_tmp) > 0) {
          $filters[$f_tmp[0]] = true;
        }
      }

      // Parse page number
      $page = intval($page);

      // dispatch charts event
      $event = new ChartEvent();
      $event->setOrderId($order_id);
      $event->setFilters($filters);
      $this
        ->container
        ->get('event_dispatcher')
        ->dispatch(ClipperEvents::CHART_PDF_PREVIEW, $event);

      // get array map of twigs and placeholders from service
      $maps = $event->getPdfMaps();

      $map = ($maps !== null) ? $maps[0]->toArray() : array();

      if ($page > count($map) || $page <= 0) {
        throw new Exception("Page does not exist. Limit is " . count($map));
      }
      $idx = $page - 1;

      $twig = isset($map[$idx]['twig']) ? $map[$idx]['twig'] : '';
      $placeholders = isset($map[$idx]['placeholders']) ? $map[$idx]['placeholders'] : array();

      $response = new \Symfony\Component\HttpFoundation\Response();
      $response->headers->set('Content-Type', 'text/html');
      return $this->render($twig, $placeholders, $response);

    }
    catch(Exception $e) {
      $content = "{$e->getMessage()} - File [{$e->getFile()}] - Line [{$e->getLine()}]";
      $code = 204;
    }

    return new Response($content, $code);
  }
  
  /**
   * PDF download in zip compressed file
   * /clipper/charts/pdfs
   *
   * @param ParamFetcher $paramFetcher
   *
   * @QueryParam(name="order_id", default="(empty)", description="collection of objects to generate multipage pdf")
   * 
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function pdfAction(ParamFetcher $paramFetcher)
  {
    $logger = $this->container->get('monolog.logger.clipper');
    $order_id = $paramFetcher->get('order_id', ''); // FQG order_id
    $filepath = $this->container->get('kernel')->getRootDir() . '/../web/bundles/pslclipper/zip/';
    $filename = 'charts-'. $order_id .'.zip';
    $filefull = $filepath . $filename;
    
    try {
      // create archive
      $archive = new \ZipArchive();
      $r = $archive->open($filefull, \ZipArchive::CREATE|\ZipArchive::OVERWRITE);
      if ($r !== true) {
        throw new Exception('Unable to create the Zip Archive.');
      }

      // dispatch charts event
      $event = new ChartEvent();
      $event->setOrderId($order_id);
      $this
        ->container
        ->get('event_dispatcher')
        ->dispatch(ClipperEvents::CHART_PDF, $event);
      
      // get array of pdf filenames from service
      $pdfs = $event->getPdfFiles();
      foreach ($pdfs as $pdffilename) {
        $local = 'charts-' . $order_id. '/';
        $archive->addFile($pdffilename, $local . pathinfo($pdffilename, PATHINFO_BASENAME));
      }  
    }
    catch (Exception $e) {
      die($e);
      $logger->debug('Pdf files error', array('exception' => $e));
      // @TODO: Remove exception from empty string
      $archive->addFromString('empty.txt', 'empty - ' . $e->getMessage());
    }
    
    $archive->close(); // write file to system, ensure 'file_get_contents' works
    
    $response = new \Symfony\Component\HttpFoundation\Response(file_get_contents($filefull));
    $d = $response->headers->makeDisposition(\Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
    $response->headers->set('Content-Disposition', $d);
    $response->headers->set('Content-Type', 'application/zip');
    $response->headers->set('Content-Length', filesize($filefull));
       
    return $response;
  }
}
