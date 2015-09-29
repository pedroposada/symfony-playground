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
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
      $drilldown = array(
        'country'   => $request->request->get('country', ''),
        'region'    => $request->request->get('region', ''),
        'specialty' => $request->request->get('specialty', ''),
      );
      $content = $this->getChartsByOrderId($order_id, $drilldown);
    }
    catch(Exception $e) {
      $content = "Message: [{$e->getMessage()}] - Line: {[$e->getLine()]}";
      $code = 204;
    }

    return new Response($content, $code);
  }

  /**
   * Download CSV
   * /clipper/charts/download
   *
   *
   * @Route("/charts/download")
   * @Method("GET")
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @QueryParam(name="order_id", default="", nullable=false, description="FirstQGroup UUID")
   * @QueryParam(name="type", default="xls", nullable=true, description="Export file type.")
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function downloadAction(ParamFetcher $paramFetcher)
  {
    $time   = microtime(TRUE);
    $memory = memory_get_peak_usage(TRUE);

    $content = null;
    $code    = 200;

    try {
      $order_id = $paramFetcher->get('order_id');
      $type     = $paramFetcher->get('type');
      $charts   = $this->getChartsByOrderId($order_id);

      //TODO review cache strategies
      //prep data structure
      $data = array(
        'complete'            => $charts,
        'available-drilldown' => array(),
        'available-brands'    => array(),
        'available-charts'    => array(),
        'charts-table-map'    => array(),
        'filtered'            => array(),
      );
      $drillbits = array(
        'countries'   => 'country',
        'specialties' => 'specialty',
        'regions'     => 'region',
      );
      foreach ($charts as $index => $chart_data) {
        foreach (array('drilldown', 'brands') as $key) {
          if (empty($data['available-' . $key])) {
            $data['available-' . $key] = $charts[0][$key];
          }
        }
        $data['available-charts'][$index] = $chart_data['chartmachinename'];
      }
      foreach ($drillbits as $drillType => $drillName) {
        if (!empty($data['available-drilldown'][$drillType])) {
          if (!isset($data['filtered'][$drillName])) {
            $data['filtered'][$drillName] = array();
          }
          foreach ($data['available-drilldown'][$drillType] as $filter) {
            $data['filtered'][$drillName][$filter] = array();
            $filter_set = array($drillName => $filter);
            $data['filtered'][$drillName][$filter] = $this->getChartsByOrderId($order_id, $filter_set);
          }
        }
      }

      // FOR DEVELOPEMENT, @todo: remove this
      if ($type == 'dev') {
        $content = array(
          'order-id'   => $order_id,
          'type'       => $type,
          'dev-time'   => microtime(TRUE) - $time,
          'dev-memory' => (((memory_get_peak_usage(TRUE) - $memory) / 1024) / 1024),
          'data-count' => count($charts),
          'data'       => $data,
        );
        goto break_try_complete;
      }

      //prep for export
      //@todo: align the needs for GET[type] to supported download types
      return $this->exportExcelOrder($order_id, $data);
    }
    catch(Exception $e) {
      $content = "Message: [{$e->getMessage()}] - Line: {[$e->getLine()]}";
      $code = 204;
    }
    break_try_complete:

    return new Response($content, $code);
  }

  /**
   *
   *
   * @param $order_id
   * @return $charts ArrayCollection
   */
  private function getChartsByOrderId($order_id, $drilldown = array())
  {
    $charts = new ArrayCollection();
    $em = $this->container->get('doctrine')->getManager();
    $fqg = $em->getRepository('PSLClipperBundle:FirstQGroup')->find($order_id);

    if (!$fqg) {
      throw new Exception("FQG with id [{$order_id}] not found");
    }

    //sanitize drilldown
    $drilldown = array_merge(
      array(
        'country'   => array(),
        'countries' => array(),
        'region'    => array(),
        'specialty' => array(),
      ),
      $drilldown
    );

    $survey_type = $fqg->getFormDataByField('survey_type');
    $survey_type = reset($survey_type);
    $map = $this->container->get('survey_chart_map')->map($survey_type);
    $assembler = $this->container->get('chart_assembler');

    foreach ($map['machine_names'] as $index => $machine_name) {
      $chEvent = $assembler->getChartEvent($order_id, $machine_name, $survey_type, $drilldown);
      $chart = array(
        'chartmachinename' => $machine_name,
        'charttype'        => $machine_name . self::$js_charttype_postfix,
        'drilldown'        => $chEvent->getDrillDown(),
        'filter'           => $chEvent->getFilters(),
        'countTotal'       => $chEvent->getCountTotal(),
        'countFiltered'    => $chEvent->getCountFiltered(),
        'brands'           => $chEvent->getBrands(),
        'datatable'        => $chEvent->getDataTable(),
      );
      $charts->add($chart);
    }

    return $charts;
  }

  /**
   * Helper method to export data into Excel file.
   * @method exportExcelOrder
   *
   * @param  string $order_id
   * @param  array $data
   *    Expected structure:
   *    'complete' => API-RESPONSE
   *    'available-drilldown' => ARRAY-WITH-DRILLDOWN-FORMAT
   *    'available-brands'    => ARRAY-LIST-OF-BRANDS
   *    'filtered' =>
   *      'country' =>
   *        'X' => API-RESPONSE
   *        'X' => API-RESPONSE
   *        ...
   *      'specialty' =>
   *        'X' => API-RESPONSE
   *        ...
   *      'region' =>
   *        ...
   *
   * @return \Symfony\Component\BrowserKit\Response
   *    might return Exception
   */
  private function exportExcelOrder($order_id, $data)
  {
    //prep object
    $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

    //@todo: rename info here
    $application = 'Clipper';
    $file_name = explode('-', $order_id);
    $file_name = 'Clipper-Export-' . $file_name[0];

    //prep document
    $phpExcelObject->getProperties()->setCreator($application)
      ->setLastModifiedBy($application)
      ->setTitle($file_name);

    //prep headings
    $data['headings'] = array(
      "TOC" => "Table of contents",
      "Table 1" => "What is the Net Promoter Score (NPS) score for my brand and my competitors?",
      "Table 2" => "How loyal are doctors to my brand?",
      "Table 3" => "How satisfied is the market?",
      "Table 4" => "Amongst doctors promoting my brand, how many other brands do they also promote?",
      "Table 5" => "Amongst my Promoters which other brands are promoted?",
      "Table 6" => "Amongst my detractors which other brands do they promote?",
      "Table 7" => "How much more of my brand do Promoters use compared to Passives and Detractors?",
      "Table 8" => "What brand messages are associated with Promoters, Passives and Detractors?",
    );
    $chartDataSequences = array(
      'Table 1' => 'NPS',
      'Table 2' => 'Loyalty',
      'Table 3' => 'DoctorsPromote',
      'Table 4' => 'PromotersPromoteMean',
      'Table 5' => 'PromotersPromote',
      'Table 6' => 'DetractorsPromote',
      'Table 7' => 'PromVsDetrPromote',
      'Table 8' => 'PPDBrandMessages',
    );
    $original_chartDataSequences = count($chartDataSequences);
    if (!empty($data['available-brands'])) {
      foreach ($data['available-brands'] as $brand) {
        $key = "Table " . count($data['headings']);
        $data['headings'][$key] = "What brand messages are associated with {$brand}?";
        $chartDataSequences[$key] = 'PPDBrandMessagesByBrands';
      }
    }

    //create sheets
    $sheet = 0;
    foreach ($data['headings'] as $key => $headings) {
      if ($sheet > 0) {
        $phpExcelObject->createSheet($sheet);
      }
      $sheet++;
    }

    //writing data
    $sheet = 0;
    foreach ($data['headings'] as $key => $headings) {
      $chartDataSequence = FALSE;
      if (isset($chartDataSequences[$key])) {
        $chartDataSequence = array_search($chartDataSequences[$key], $data['available-charts']);
      }
      $brand = FALSE;
      if (($sheet - $original_chartDataSequences) >= 1) {
        $brand = ($sheet - $original_chartDataSequences) - 1;
      }
      $this->excelWriteASheet($key, $sheet, $data, $chartDataSequence, $phpExcelObject, $brand);
      $sheet++;
    }

    //move to TOC
    $phpExcelObject->setActiveSheetIndex(0);

    //stream the file
    $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
    //stops all output; notice errors / whitespace & etc which render filename bug
    ob_end_clean();
    $response = $this->get('phpexcel')->createStreamedResponse($writer);
    $dispositionHeader = $response->headers->makeDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        "{$file_name}.xls"
    );
    $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
    $response->headers->set('Pragma', 'public');
    $response->headers->set('Cache-Control', 'maxage=1');
    $response->headers->set('Content-Disposition', $dispositionHeader);
    $response->headers->set('Content-Transfer-Encoding', 'binary');
    return $response;
  }

  /**
   * Helper Method to write a Sheet basic structure.
   * @method excelWriteASheet
   *
   * @param  string $sheetname
   * @param  int $sheetnum
   * @param  array $data
   * @param  object PHPExcel_Worksheet $excelObject
   * @param  boolean|int $brandSpecific
   *
   * @return void
   */
  private function excelWriteASheet($sheetname, $sheetnum, $data, $chartDataSequence = FALSE, &$excelObject, $brandSpecific = FALSE)
  {
    //pre select an active sheet
    $activeWorkSheet = $excelObject->setActiveSheetIndex($sheetnum);

    switch ($sheetname) {
      // TOC
      case 'TOC':
        //heading
        $activeWorkSheet->setCellValue('A1', 'TABLE OF CONTENTS');
        $activeWorkSheet->getStyle('A1')->applyFromArray(
          array(
            'fill' => array(
              'type'  => \PHPExcel_Style_Fill::FILL_SOLID,
              'color' => array('rgb' => 'CCCCCC'),
            ),
            'font' => array(
              'color' => array('rgb' => 'FFFFFF'),
              'size'  => 13,
            ),
          )
        );
        $activeWorkSheet->getColumnDimension('A')->setWidth(2400);

        //subheading

        //content
        $ind = 3;
        foreach ($data['headings'] as $key => $heading) {
          if ($key == $sheetname) {
            continue;
          }
          $activeWorkSheet->setCellValue("A{$ind}", "{$key} {$heading}");
          $activeWorkSheet->getCell("A{$ind}")->getHyperlink()->setUrl("sheet://'{$key}'!A1");
          $ind++;
        }


        //drilldown
        break;

      // NPS Chart
      case 'Table 1':
      // Loyalty Chart
      case 'Table 2':
      // PromotersPromoteMean Chart
      case 'Table 4':
      // PromotersPromote Chart
      case 'Table 5':
      // DetractorsPromote Chart
      case 'Table 6':
      // PromVsDetrPromote Chart
      case 'Table 7':
        //data prep
        if ($chartDataSequence === FALSE) {
          throw new Exception("{$sheetname} has no data");
        }

        //heading
        $activeWorkSheet->setCellValue('A1', "{$sheetname} {$data['headings'][$sheetname]}");

        //subheading
        $activeWorkSheet->setCellValue('A2', "Base: All respondents who are aware of the brands.");

        //content
        $activeWorkSheet->getColumnDimension('A')->setWidth(20);
        $row = 5; //stats at
        $this->excelDrawTable($sheetname, $data['complete'][$chartDataSequence], $activeWorkSheet, $row, $brandSpecific);


        //drilldown
        foreach ($data['filtered'] as $type => $filters) {
          foreach ($filters as $filter => $filtered_data) {
            $row += 3; //space between charts
            $drilldownHeading = ucwords($type);
            $this->excelDrawTable($sheetname, $filtered_data[$chartDataSequence], $activeWorkSheet, $row, $brandSpecific, "Drilldown {$drilldownHeading}: {$filter}");
          }
        }
        break;

      // DoctorsPromote Chart
      case 'Table 3':
        //data prep
        if ($chartDataSequence === FALSE) {
          throw new Exception("{$sheetname} has no data");
        }

        //heading
        $activeWorkSheet->setCellValue('A1', "{$sheetname} {$data['headings'][$sheetname]}");

        //subheading
        $activeWorkSheet->setCellValue('A2', "Base: All respondents who are aware of the brands.");

        //content
        $activeWorkSheet->getColumnDimension('A')->setWidth(30);
        $activeWorkSheet->getColumnDimension('B')->setWidth(30);
        $row = 5; //stats at
        $this->excelDrawTable($sheetname, $data['complete'][$chartDataSequence], $activeWorkSheet, $row, $brandSpecific);


        //drilldown
        foreach ($data['filtered'] as $type => $filters) {
          foreach ($filters as $filter => $filtered_data) {
            $row += 3; //space between charts
            $drilldownHeading = ucwords($type);
            $this->excelDrawTable($sheetname, $filtered_data[$chartDataSequence], $activeWorkSheet, $row, $brandSpecific, "Drilldown {$drilldownHeading}: {$filter}");
          }
        }
        break;

      // PPDBrandMessages Chart
      case 'Table 8':
      // PPDBrandMessagesByBrands Chart
      case ($brandSpecific !== FALSE): //Table 9 to any number of brands
        //data prep
        if ($chartDataSequence === FALSE) {
          throw new Exception("{$sheetname} has no data");
        }

        //heading
        $activeWorkSheet->setCellValue('A1', "{$sheetname} {$data['headings'][$sheetname]}");

        //subheading
        if ($brandSpecific === FALSE) { // Table 8
          $activeWorkSheet->setCellValue('A2', "Base: All respondents.");
        }
        else {
          $activeWorkSheet->setCellValue('A2', "Base: All respondents who are aware of {$data['available-brands'][$brandSpecific]}.");
        }

        //content
        $activeWorkSheet->getColumnDimension('A')->setWidth(30);
        $row = 5; //stats at
        $this->excelDrawTable($sheetname, $data['complete'][$chartDataSequence], $activeWorkSheet, $row, $brandSpecific);

        //drilldown
        foreach ($data['filtered'] as $type => $filters) {
          foreach ($filters as $filter => $filtered_data) {
            $row += 3; //space between charts
            $drilldownHeading = ucwords($type);
            $this->excelDrawTable($sheetname, $filtered_data[$chartDataSequence], $activeWorkSheet, $row, $brandSpecific, "Drilldown {$drilldownHeading}: {$filter}");
          }
        }
        break;
    } //switch $sheetname

    //set sheet name
    $activeWorkSheet->setTitle($sheetname);
    //reset cursor
    $activeWorkSheet->setSelectedCells('A1');
  }

  /**
   * Helper method to draw specific table
   * @method excelDrawTable
   *
   * @param  string $sheetname
   * @param  array $dataTable
   * @param  object PHPExcel_Worksheet &$activeWorkSheet
   * @param  integer &$row
   * @param  boolean|int $brandSpecific
   * @param  boolean|string $drilldown
   *    Flag | Label to identify if table meant for drilldown.
   *
   * @return void
   */
  private function excelDrawTable($sheetname, $dataTable = array(), &$activeWorkSheet, &$row = 4, $brandSpecific = FALSE, $drilldown = FALSE)
  {
    $net_cats = array('detractor', 'passive', 'promoter');
    $col = range('A', 'Z');
    $style = array(
      'def-border' => array(
        'style' => \PHPExcel_Style_Border::BORDER_THIN,
        'color' => array('rgb' => '000000'),
      ),
      'def-note'   => array(
        'font'  => array(
          'italic' => TRUE,
          'color'  => array('rgb' => '555555'),
          'size'   => 10,
        ),
      ),
      'align-center' => array(
        'alignment' => array(
          'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical'   => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
      ),
    );

    if ($drilldown !== FALSE) {
      //drilldown heading first
      $activeWorkSheet->setCellValue("A{$row}", "{$drilldown}");
      $activeWorkSheet->getStyle("A{$row}")->getFont()->setBold(TRUE);
      $row++;
    }

    //draw table
    switch ($sheetname) {
      // NPS Chart
      case 'Table 1':
        //prep data
        $localDataTable = array();
        array_walk($dataTable['datatable'], function($set, $key) use (&$localDataTable) {
          $localDataTable[$set['brand']] = $set;
        });

        $rowStarts = $row;

        // brand list
        $colStarts = $alp = 1;
        foreach($dataTable['brands'] as $brand) {
          $activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$brand}");
          $colEnds = $alp;
          $alp++;
        }
        $row++;

        //base
        $activeWorkSheet->setCellValue("A{$row}", "Base");
        $alp = 1;
        foreach($dataTable['brands'] as $brand) {
          $activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$localDataTable[$brand]['base']}");
          $alp++;
        }
        $row++;
        $row++;

        //net
        foreach ($net_cats as $type) {
          $type .= 's';
          $cattype = ucwords($type);
          $activeWorkSheet->setCellValue("A{$row}", $cattype);
          $alp = 1;
          foreach($dataTable['brands'] as $brand) {
            $activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$localDataTable[$brand][$type]}%");
            $alp++;
          }
          $row++;
        }
        $row++;

        //mean
        $activeWorkSheet->setCellValue("A{$row}", "Mean scores");

        $alp = 1;
        foreach($dataTable['brands'] as $brand) {
          $activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$localDataTable[$brand]['score']}");
          $alp++;
        }

        $end_row = $row;

        //styling
        $activeWorkSheet->getStyle("{$col[$colStarts]}{$rowStarts}:{$col[$colEnds]}{$end_row}")->applyFromArray($style['align-center']);
        $activeWorkSheet->getStyle("{$col[$colStarts]}{$rowStarts}:{$col[$colEnds]}{$rowStarts}")->getFont()->setBold(TRUE);
        $activeWorkSheet->getStyle("A{$end_row}:{$col[$colEnds]}{$end_row}")->getFont()->setBold(TRUE);
        $styles = array('borders' => array('bottom' => $style['def-border']));
        $activeWorkSheet->getStyle("A{$rowStarts}:{$col[$colEnds]}{$rowStarts}")->applyFromArray($styles);
        $styles = array('borders' => array('right' => $style['def-border']));
        $activeWorkSheet->getStyle("A{$rowStarts}:A{$end_row}")->applyFromArray($styles);
        break;

      // Loyalty Chart
      case 'Table 2':
        //prep data
        $localDataTable = array();
        array_walk($dataTable['datatable']['brands'], function($set, $key) use (&$localDataTable) {
          $localDataTable[$set['brand']] = $set;
        });

        $rowStarts = $row;

        //heading
        $activeWorkSheet->setCellValue("B{$row}", "Base");
        $activeWorkSheet->setCellValue("C{$row}", "Mean");
        $row++;

        //brands
        foreach($dataTable['brands'] as $brand) {
          //sanitize
          if (!isset($localDataTable[$brand])) {
            $localDataTable[$brand] = array();
          }
          foreach (array('base', 'loyalty') as $type) {
            if (!isset($localDataTable[$brand][$type])) {
              $localDataTable[$brand][$type] = 0;
            }
          }
          $activeWorkSheet->setCellValue("A{$row}", "{$brand}");
          $activeWorkSheet->setCellValue("B{$row}", "{$localDataTable[$brand]['base']}");
          $activeWorkSheet->setCellValue("C{$row}", "{$localDataTable[$brand]['loyalty']}");
          $row++;
        }
        //mean - all brands
        $activeWorkSheet->setCellValue("A{$row}", "Mean - all brands");
        $activeWorkSheet->setCellValue("B{$row}", "{$dataTable['datatable']['base']}");
        $activeWorkSheet->setCellValue("C{$row}", "{$dataTable['datatable']['mean']}");

        $end_row = $row;

        //styling
        $styles = array('borders' => array('bottom' => $style['def-border']));
        $activeWorkSheet->getStyle("A{$rowStarts}:C{$rowStarts}")->applyFromArray($styles);
        $styles = array('borders' => array('left' => $style['def-border']), 'alignment' => $style['align-center']['alignment']);
        $activeWorkSheet->getStyle("B{$rowStarts}:B{$end_row}")->applyFromArray($styles);
        $activeWorkSheet->getStyle("C{$rowStarts}:C{$end_row}")->applyFromArray($styles);
        break;

      // DoctorsPromote Chart
      case 'Table 3':
        $rowStarts = $row;

        //heading
        $activeWorkSheet->setCellValue("B{$row}", "Number of brands promoted");
        $row++;

        //content
        $activeWorkSheet->setCellValue("A{$row}", "Base");
        $activeWorkSheet->setCellValue("B{$row}", "? (@todo)"); // @todo: verify this data
        $row++;

        $activeWorkSheet->setCellValue("A{$row}", "Non Promoters \r\n(no brands promoted)");
        $activeWorkSheet->setCellValue("B{$row}", "{$dataTable['datatable']['dissatisfied']['amount']}%");
        $row++;

        $activeWorkSheet->setCellValue("A{$row}", "Promoters\r\n(at least one brand)");
        $activeWorkSheet->setCellValue("B{$row}", "{$dataTable['datatable']['satisfied']['amount']}%");
        $row++;

        $activeWorkSheet->setCellValue("A{$row}", "Exclusive \r\n(only one brand promoted)");
        $activeWorkSheet->setCellValue("B{$row}", "{$dataTable['datatable']['satisfied']['exclusive']['amount']}%");
        $row++;

        $activeWorkSheet->setCellValue("A{$row}", "Shared \r\n(more than one brand promoted)");
        $activeWorkSheet->setCellValue("B{$row}", "{$dataTable['datatable']['satisfied']['shared']['amount']}%");

        $end_row = $row;

        //styling
        $styles = array('borders' => array('right' => $style['def-border']));
        $activeWorkSheet->getStyle("A{$rowStarts}:A{$end_row}")->applyFromArray($styles);
        $styles = array('borders' => array('bottom' => $style['def-border']));
        $activeWorkSheet->getStyle("A{$rowStarts}:B{$rowStarts}")->applyFromArray($styles);
        $activeWorkSheet->getStyle("B{$rowStarts}:B{$end_row}")->applyFromArray($style['align-center']);
        $activeWorkSheet->getStyle("B{$rowStarts}:B{$end_row}")->getAlignment()->setWrapText(TRUE);
        foreach (range(($rowStarts + 2), $end_row) as $rw) {
          $activeWorkSheet->getRowDimension($rw)->setRowHeight(35);
        }
        break;

      // PromotersPromoteMean Chart
      case 'Table 4':
        $rowStarts = $row;

        //heading
        $activeWorkSheet->setCellValue("B{$row}", "Base");
        $activeWorkSheet->setCellValue("C{$row}", "Mean");
        $row++;

        //brands
        foreach($dataTable['datatable']['brands'] as $brand => $set) {
          $activeWorkSheet->setCellValue("A{$row}", "{$brand}");
          $activeWorkSheet->setCellValue("B{$row}", "{$set['base']}");
          $activeWorkSheet->setCellValue("C{$row}", "{$set['mean']}");
          $row++;
        }
        //mean - all brands
        $activeWorkSheet->setCellValue("A{$row}", "Mean - all brands");
        $activeWorkSheet->setCellValue("B{$row}", "{$dataTable['datatable']['overall']['base']}");
        $activeWorkSheet->setCellValue("C{$row}", "{$dataTable['datatable']['overall']['mean']}");

        $end_row = $row;

        //styling
        $styles = array('borders' => array('bottom' => $style['def-border']));
        $activeWorkSheet->getStyle("A{$rowStarts}:C{$rowStarts}")->applyFromArray($styles);
        $styles = array('borders' => array('left' => $style['def-border']), 'alignment' => $style['align-center']['alignment']);
        $activeWorkSheet->getStyle("B{$rowStarts}:B{$end_row}")->applyFromArray($styles);
        $activeWorkSheet->getStyle("C{$rowStarts}:C{$end_row}")->applyFromArray($styles);
        break;

      // PromotersPromote Chart
      case 'Table 5':
      // DetractorsPromote Chart
      case 'Table 6':
        //prep data
        $localDataTable = array();
        array_walk($dataTable['datatable'], function($set, $key) use (&$localDataTable) {
          $localDataTable[$set['brand']] = $set;
        });

        $rowStarts = $row;

        // brand list
        $colStarts = $alp = 1;
        foreach($dataTable['brands'] as $brand) {
          $activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$brand}");
          $colEnds = $alp;
          $alp++;
        }
        $row++;

        //base
        $activeWorkSheet->setCellValue("A{$row}", "Base");
        $alp = 1;
        foreach($dataTable['brands'] as $brand) {
          $activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$localDataTable[$brand]['base']}");
          $alp++;
        }
        $row++;

        //brands
        foreach($dataTable['brands'] as $brand) {
          $activeWorkSheet->setCellValue("A{$row}", "{$brand}");
          //competitors
          $competitors = array();
          $localDataTable[$brand]['competitors'] = (array) $localDataTable[$brand]['competitors'];
          if (!empty($localDataTable[$brand]['competitors'])) {
            $competitors = array_keys($localDataTable[$brand]['competitors']);
          }
          $alp = 0;
          foreach($dataTable['brands'] as $compbrand) {
            $alp++;
            if ($brand == $compbrand) {
              $activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "--");
              continue;
            }
            $prec = 0;
            if ((!empty($competitors)) && (in_array($compbrand, $competitors))) {
              $prec = $localDataTable[$brand]['competitors'][$compbrand];
            }
            $activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$prec}%");
          }
          $row++;
        }

        $end_row = ($row - 1);

        //styling
        $activeWorkSheet->getStyle("{$col[$colStarts]}{$rowStarts}:{$col[$colEnds]}{$end_row}")->applyFromArray($style['align-center']);
        $activeWorkSheet->getStyle("{$col[$colStarts]}{$rowStarts}:{$col[$colEnds]}{$rowStarts}")->getFont()->setBold(TRUE);
        $styles = array('borders' => array('bottom' => $style['def-border']));
        $activeWorkSheet->getStyle("A{$rowStarts}:{$col[$colEnds]}{$rowStarts}")->applyFromArray($styles);
        $styles = array('borders' => array('right' => $style['def-border']));
        $activeWorkSheet->getStyle("A{$rowStarts}:A{$end_row}")->applyFromArray($styles);
        break;

      // PromVsDetrPromote Chart
      case 'Table 7':
        //prep data
        $localDataTable = array();
        array_walk($dataTable['datatable'], function($set, $key) use (&$localDataTable) {
          $localDataTable[$set['brand']] = $set;
        });

        $rowStarts = $row;
        $colStarts = 1;
        $uppur_border = array();

        //heading 1
        $alp = (count($net_cats));
        $activeWorkSheet->mergeCells("{$col[$colStarts]}{$row}:{$col[$alp]}{$row}");
        $activeWorkSheet->setCellValue("{$col[$colStarts]}{$row}", "Market share");
        $row++;

        //heading 2
        $alp = 1;
        foreach ($net_cats as $cat) {
          $cat = ucwords($cat) . 's';
          $activeWorkSheet->setCellValue("{$col[$alp]}{$row}", $cat);
          $activeWorkSheet->getColumnDimension("{$col[$alp]}")->setWidth(12);
          $colEnds = $alp;
          $alp++;
        }
        $row++;

        //brands
        foreach ($dataTable['brands'] as $brand) {
          $uppur_border[] = $row;

          //base
          $activeWorkSheet->setCellValue("A{$row}", "Base");
          $alp = 1;
          foreach ($net_cats as $cat) {
            $cat = $cat . 's_count';
            $activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$localDataTable[$brand][$cat]}");
            $alp++;
          }
          $row++;

          //perc
          $activeWorkSheet->setCellValue("A{$row}", "{$brand}");
          $alp = 1;
          foreach ($net_cats as $cat) {
            $cat = $cat . 's_prec';
            $activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$localDataTable[$brand][$cat]}%");
            $alp++;
          }
          $row++;
          $row++;
        }

        //footer
        $activeWorkSheet->setCellValue("A{$row}", "Caution: small base sizes in some cells.");
        $activeWorkSheet->getStyle("A{$row}")->applyFromArray($style['def-note']);

        $end_row = ($row - 1);

        //styling
        $activeWorkSheet->getStyle("{$col[$colStarts]}{$rowStarts}:{$col[$colEnds]}{$end_row}")->applyFromArray($style['align-center']);
        $styles = array('borders' => array('right' => $style['def-border']));
        $activeWorkSheet->getStyle("A{$rowStarts}:A{$end_row}")->applyFromArray($styles);
        if (!empty($uppur_border)) {
          $styles = array('borders' => array('top' => $style['def-border']));
          foreach ($uppur_border as $row_ind) {
            $activeWorkSheet->getStyle("A{$row_ind}:{$col[$colEnds]}{$row_ind}")->applyFromArray($styles);
          }
        }
        break;

      // PPDBrandMessages Chart
      case 'Table 8':
        if (empty($dataTable['datatable'])) {
          $activeWorkSheet->setCellValue("A{$row}", "No result.");
          return;
        }
        $rowStarts = $row;
        $colStarts = 1;
        $uppur_border = array();

        //heading 1
        $alp = (count($net_cats));
        $activeWorkSheet->mergeCells("{$col[$colStarts]}{$row}:{$col[$alp]}{$row}");
        $activeWorkSheet->setCellValue("{$col[$colStarts]}{$row}", "All Brands");
        $row++;

        //heading 2
        $alp = 1;
        foreach ($net_cats as $cat) {
          $cat = ucwords($cat) . 's';
          $activeWorkSheet->setCellValue("{$col[$alp]}{$row}", $cat);
          $activeWorkSheet->getColumnDimension("{$col[$alp]}")->setWidth(12);
          $colEnds = $alp;
          $alp++;
        }
        $row++;

        //base, continue later
        $base_row = $row;
        $base_count = array();
        $activeWorkSheet->setCellValue("A{$row}", "Base");
        $row++;

        //messages
        foreach ($dataTable['datatable'] as $msgInd => $mesge) {
          $activeWorkSheet->setCellValue("A{$row}", "{$mesge['message']}");
          $alp = 1;
          foreach ($net_cats as $cat) {
            //get count
            $count = $cat . 's_count';
            if (!isset($base_count[$cat])) {
              $base_count[$cat] = 0;
            }
            $base_count[$cat] += $mesge[$count];

            //perc
            $perc = $cat . 's';
            $activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$mesge[$perc]}%");
            $alp++;
          }
          $row++;
        }
        $row--;

        $end_row = $row;

        //base
        $alp = 1;
        foreach ($net_cats as $cat) {
          $activeWorkSheet->setCellValue("{$col[$alp]}{$base_row}", "{$base_count[$cat]}");
          $alp++;
        }

        //styling
        $activeWorkSheet->getStyle("{$col[$colStarts]}{$rowStarts}:{$col[$colEnds]}{$end_row}")->applyFromArray($style['align-center']);
        $styles = array('borders' => array('right' => $style['def-border']));
        $activeWorkSheet->getStyle("A{$rowStarts}:A{$end_row}")->applyFromArray($styles);
        $styles = array('borders' => array('bottom' => $style['def-border']));
        $activeWorkSheet->getStyle("A{$base_row}:{$col[$colEnds]}{$base_row}")->applyFromArray($styles);
        $base_row--;
        $activeWorkSheet->getStyle("A{$base_row}:{$col[$colEnds]}{$base_row}")->applyFromArray($styles);
        break;

      // PPDBrandMessagesByBrands Chart
      case ($brandSpecific !== FALSE): //Table 9 ~ to any number of brands
        $rowStarts = $row;
        $colStarts = 1;
        $uppur_border = array();

        //heading 1
        $alp = (count($net_cats));
        $activeWorkSheet->mergeCells("{$col[$colStarts]}{$row}:{$col[$alp]}{$row}");
        $activeWorkSheet->setCellValue("{$col[$colStarts]}{$row}", "{$dataTable['brands'][$brandSpecific]}");
        $row++;

        //heading 2
        $alp = 1;
        foreach ($net_cats as $cat) {
          $cat = ucwords($cat) . 's';
          $activeWorkSheet->setCellValue("{$col[$alp]}{$row}", $cat);
          $activeWorkSheet->getColumnDimension("{$col[$alp]}")->setWidth(12);
          $colEnds = $alp;
          $alp++;
        }
        $row++;

        //base, continue later
        $base_row = $row;
        $base_count = array();
        $activeWorkSheet->setCellValue("A{$row}", "Base");
        $row++;

        //messages
        foreach ($dataTable['datatable']['questions'] as $questionIndex => $question) {
          $activeWorkSheet->setCellValue("A{$row}", "{$question}");
          $brand = $dataTable['brands'][$brandSpecific];
          $scores = $dataTable['datatable']['brands'][$brand][$questionIndex];
          $alp = 1;
          foreach ($net_cats as $cat) {
            if (!isset($base_count[$cat])) {
              $base_count[$cat] = 0;
            }
            $base_count[$cat] += $scores[$cat]['base'];
            $activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$scores[$cat]['perc']}%");
            $alp++;
          }
          $row++;
        }

        //footer
        $activeWorkSheet->setCellValue("A{$row}", "Caution: small base sizes in some cells.");
        $activeWorkSheet->getStyle("A{$row}")->applyFromArray($style['def-note']);
        $row--;

        $end_row = $row;

        //base
        $alp = 1;
        foreach ($net_cats as $cat) {
          $activeWorkSheet->setCellValue("{$col[$alp]}{$base_row}", "{$base_count[$cat]}");
          $alp++;
        }

        //styling
        $activeWorkSheet->getStyle("{$col[$colStarts]}{$rowStarts}:{$col[$colEnds]}{$end_row}")->applyFromArray($style['align-center']);
        $styles = array('borders' => array('right' => $style['def-border']));
        $activeWorkSheet->getStyle("A{$rowStarts}:A{$end_row}")->applyFromArray($styles);
        $styles = array('borders' => array('bottom' => $style['def-border']));
        $activeWorkSheet->getStyle("A{$base_row}:{$col[$colEnds]}{$base_row}")->applyFromArray($styles);
        $base_row--;
        $activeWorkSheet->getStyle("A{$base_row}:{$col[$colEnds]}{$base_row}")->applyFromArray($styles);
        break;
    } //switch $sheetname

    $row++;
  }
}
