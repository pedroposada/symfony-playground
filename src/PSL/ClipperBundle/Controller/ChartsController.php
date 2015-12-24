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
      $f_arr = split(',', $drilldown);
      foreach ($f_arr as $value) {
        $f_tmp = split(':', $value);
        if (count($f_tmp) > 1) {
          $filters[$f_tmp[0]] = $f_tmp[1];
        } else if (count($f_tmp) > 0) {
          $filters[$f_tmp[0]] = true;
        }
      }

      // Parse page number
      // @TODO Use #.# notation maybe
      $page = intval($page);

      // @TODO Uncomment when NpsPlusPdf is ready
      // dispatch charts event
      // $event = new ChartEvent();
      // $event->setOrderId($order_id);
      // $this
      //   ->container
      //   ->get('event_dispatcher')
      //   ->dispatch(ClipperEvents::CHART_PDF, $event);
      // get array map of twigs and placeholders from service
      // $map = $event->getPdfMap();
      
      // Hardcoded twig map.
      // @TODO Remove when prev. section is ready
      $map = array(
        array(
          'twig' => 'PSLClipperBundle:Charts:nps_plus/introduction.html.twig',
          'placeholders' => array(
            'main_title' => 'NPS+ Multiple Sclerosis',
          )
        ),
        array(
          'twig' => 'PSLClipperBundle:Charts:nps_plus/tableofcontents.html.twig'
        ),
        array(
          'twig' => 'PSLClipperBundle:Charts:nps_plus/chart01.html.twig',
          'placeholders' => array(
            'chart_datatable' => '[
              {
                "brand": "Tecfidera",
                "detractors": 27,
                "passives": 40,
                "promoters": 34,
                "score": 7
              },
              {
                "brand": "Tysabri",
                "detractors": 37,
                "passives": 34,
                "promoters": 29,
                "score": -8
              },
              {
                "brand": "Copaxone",
                "detractors": 35,
                "passives": 39,
                "promoters": 26,
                "score": -9
              },
              {
                "brand": "Rebif",
                "detractors": 42,
                "passives": 38,
                "promoters": 20,
                "score": -22
              },
              {
                "brand": "Gilenya",
                "detractors": 45,
                "passives": 40,
                "promoters": 15,
                "score": -29
              }
            ]'
          )
        ),
        array(
          'twig' => 'PSLClipperBundle:Charts:nps_plus/chart02.html.twig',
          'placeholders' => array(
            'chart_datatable' => '{
               "mean": 2.05,
               "brands":[
                  {
                    "brand": "Tecfidera",
                    "loyalty": 2.38
                  },
                  {
                    "brand": "Tysabri",
                    "loyalty": 2.14
                  },
                  {
                    "brand": "Copaxone",
                    "loyalty": 2.08
                  },
                  {
                    "brand": "Rebif",
                    "loyalty": 1.88
                  },
                  {
                    "brand": "Gilenya",
                    "loyalty": 1.78
                  }
               ]
            }'
          )
        ),
        array(
          'twig' => 'PSLClipperBundle:Charts:nps_plus/chart03.html.twig',
          'placeholders' => array(
            'chart_datatable' => '{
               "satisfied":{
                  "amount":52,
                  "exclusive":{
                     "amount":15
                  },
                  "shared":{
                     "amount":36
                  }
               },
               "dissatisfied":{
                  "amount":48
               }
            }'
          )
        ),
        array(
          'twig' => 'PSLClipperBundle:Charts:nps_plus/chart04.html.twig',
          'placeholders' => array(
            'chart_datatable' => '{
               "overall": {
                  "base": 11,
                  "mean": 0.9
                },
               "brands": {
                  "Tysabri": {
                    "base": 3,
                    "mean": 1
                  },
                  "Tecfidera": {
                    "base": 3,
                    "mean": 1
                  },
                  "Gilenya": {
                    "base": 2,
                    "mean": 1.1
                  },
                  "Rebif": {
                    "base": 2,
                    "mean": 1.1
                  },
                  "Copaxone": {
                    "base": 1,
                    "mean": 1.3
                  }
                }
            }'
          )
        ),
        array(
          'twig' => 'PSLClipperBundle:Charts:nps_plus/chart05.html.twig',
          'placeholders' => array(
            'chart_datatable' => '[
               {
                 "brand": "Tysabri",
                 "competitors": {
                   "Tecfidera": 67
                 }
               },
               {

                 "brand": "Tecfidera",
                 "competitors": {
                   "Tysabri": 58
                 }
               },
               {
                 "brand": "Rebif",
                 "competitors": {
                   "Copaxone": 87
                 }
               },
               {
                 "brand": "Copaxone",
                 "competitors": {
                   "Rebif": 68
                 }
               },
               {
                 "brand": "Gilenya",
                 "competitors": {
                   "Tecfidera": 83
                 }
               }
            ]'
          )
        ),
        array(
          'twig' => 'PSLClipperBundle:Charts:nps_plus/chart06.html.twig',
          'placeholders' => array(
            'chart_datatable' => '[
               {
                 "brand": "Tysabri",
                 "competitors": {
                   "Tecfidera": 12,
                   "Rebif": 7,
                   "Copaxone": 11,
                   "Gilenya": 5
                 }
               },
               {
                 "brand": "Tecfidera",
                 "competitors": {
                   "Tysabri": 7,
                   "Rebif": 17,
                   "Copaxone": 22,
                   "Gilenya": 2
                 }
               },
               {
                 "brand": "Rebif",
                 "competitors": {
                   "Tysabri": 22,
                   "Tecfidera": 25,
                   "Copaxone": 9,
                   "Gilenya": 6
                 }
               },
               {
                 "brand": "Copaxone",
                 "competitors": {
                   "Tysabri": 20,
                   "Tecfidera": 20,
                   "Rebif": 2,
                   "Gilenya": 4
                 }
               },
               {
                 "brand": "Gilenya",
                 "competitors": {
                   "Tecfidera": 16,
                   "Tysabri": 22,
                   "Copaxone": 6,
                   "Rebif": 12
                 }
               }
            ]'
          )
        ),
        array(
          'twig' => 'PSLClipperBundle:Charts:nps_plus/chart07.html.twig',
          'placeholders' => array(
            'chart_datatable' => '[
               {
                  "brand":"Tysabri",
                  "promoters": 11.8,
                  "detractors": 2.6,
                  "diff": 354
               },
               {
                  "brand":"Tecfidera",
                  "promoters": 16.1,
                  "detractors": 4.4,
                  "diff": 266
               },
               {
                  "brand":"Gilenya",
                  "promoters": 7.3,
                  "detractors": 2.7,
                  "diff": 170
               },
               {
                  "brand":"Rebif",
                  "promoters": 24.5,
                  "detractors": 9.4,
                  "diff": 161
               },
               {
                  "brand":"Copaxone",
                  "promoters": 33,
                  "detractors": 18.5,
                  "diff": 78
               }
            ]'
          )
        ),
        array(
          'twig' => 'PSLClipperBundle:Charts:nps_plus/chart08intro.html.twig',
          'placeholders' => array(
            'region' => 'EU5'
          )
        ),
        array(
          'twig' => 'PSLClipperBundle:Charts:nps_plus/chart08.html.twig',
          'placeholders' => array(
            'subsection_number' => '1',
            'brand' => 'Aubagio (teriflunomide)',
            'chart_datatable' => '[
               {
                  "message":"Is cost effective",
                  "detractors":46,
                  "passives":68,
                  "promoters":81
               },
               {
                  "message":"Has few side effects",
                  "detractors":44,
                  "passives":68,
                  "promoters":78
               },
               {
                  "message":"Good overall benefits v risk profile",
                  "detractors":67,
                  "passives":92,
                  "promoters":94
               },
               {
                  "message":"Has convenient administration",
                  "detractors":53,
                  "passives":64,
                  "promoters":80
               },
               {
                  "message":"Provides long term safety",
                  "detractors":61,
                  "passives":76,
                  "promoters":86
               },
               {
                  "message":"Allows for good patient compliance",
                  "detractors":70,
                  "passives":73,
                  "promoters":92
               },
               {
                  "message":"Has novel mechanism of action",
                  "detractors":62,
                  "passives":65,
                  "promoters":83
               },
               {
                  "message":"Sustains long term efficacy",
                  "detractors":72,
                  "passives":79,
                  "promoters":89
               },
               {
                  "message":"Positively impacts quality of life",
                  "detractors":81,
                  "passives":90,
                  "promoters":97
               },
               {
                  "message":"Is well tolerated",
                  "detractors":68,
                  "passives":84,
                  "promoters":84
               },
               {
                  "message":"Slows the progression of disability",
                  "detractors":80,
                  "passives":87,
                  "promoters":94
               },
               {
                  "message":"Reduces the number / severity of relapses",
                  "detractors":93,
                  "passives":94,
                  "promoters":99
               }
            ]'
          )
        ),
        array(
          'twig' => 'PSLClipperBundle:Charts:nps_plus/chart09intro.html.twig'
        ),
        array(
          'twig' => 'PSLClipperBundle:Charts:nps_plus/chart09.html.twig',
          'placeholders' => array(
            'subsection_number' => '1',
            'brand' => 'Aubagio',
            'country' => 'FRANCE',
            'chart_datatable' => '[
               {
                  "brand":"Tecfidera",
                  "detractors":[
                     "Expensive now old-fashioned inhaler",
                     "Second/third line treatment for more difficult patients",
                     "Expensive and sometimes helps",
                     "Expensive, been around long time",
                     "2nd line treatment some cons as high steroid"
                  ],
                  "promoters":[
                     "A trusted first line therapy",
                     "Prevention of symptoms and improvement in lung function",
                     "Gold standard",
                     "Treats symptoms and reduces exacerbations",
                     "Keeps patients out of hospital"
                  ]
               },
               {
                  "brand":"Tysabri",
                  "detractors":[
                     "Expensive now old-fashioned inhaler",
                     "Second/third line treatment for more difficult patients",
                     "Expensive and sometimes helps",
                     "Expensive, been around long time",
                     "2nd line treatment some cons as high steroid"
                  ],
                  "promoters":[
                     "A trusted first line therapy",
                     "Prevention of symptoms and improvement in lung function",
                     "Gold standard",
                     "Treats symptoms and reduces exacerbations",
                     "Keeps patients out of hospital"
                  ]
               },
               {
                  "brand":"Copaxone",
                  "detractors":[
                     "Expensive now old-fashioned inhaler",
                     "Second/third line treatment for more difficult patients",
                     "Expensive and sometimes helps",
                     "Expensive, been around long time",
                     "2nd line treatment some cons as high steroid"
                  ],
                  "promoters":[
                     "A trusted first line therapy",
                     "Prevention of symptoms and improvement in lung function",
                     "Gold standard",
                     "Treats symptoms and reduces exacerbations",
                     "Keeps patients out of hospital"
                  ]
               }
            ]'
          )
        ),
        array(
          'twig' => 'PSLClipperBundle:Charts:nps_plus/appendix.html.twig',
          'placeholders' => array()
        ),
      );

      if ($page > count($map) || $page <= 0) {
        throw new Exception("Page does not exist");
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
    $filename = 'charts.zip';
    $filefull = $filepath . $filename;

    $archive = new \ZipArchive();
    $archive->open($filefull, \ZipArchive::CREATE|\ZipArchive::OVERWRITE);
    
    try {
      // dispatch charts event
      $event = new ChartEvent();
      $event->setOrderId($order_id);
      $this
        ->container
        ->get('event_dispatcher')
        ->dispatch(ClipperEvents::CHART_PDF, $event);
      
      
      // get array of pdf filenames from service
      $pdfs = $event->getPdfFiles();
      foreach ($pdfs as $key => $filename) {
        $archive->addFile($filename);
      }  
    }
    catch (Exception $e) {
      $logger->debug('Pdf files error', array('exception' => $e));
      $archive->addFromString('empty.txt', 'empty');
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
