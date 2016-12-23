<?php
/**
 * NPSPlusExcel
 *
 * Survey Type  = NPS Plus
 * Machine Name = nps_plus
 * Export Into  = Microsoft Excel file format (xls)
 * Service Name = clipper.download.nps_plus_excel
 */
namespace PP\SampleBundle\Downloads\Types;

use PP\SampleBundle\Downloads\Types\DownloadType;
use PP\SampleBundle\Event\DownloadEvent;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class NPSPlusExcel extends DownloadType
{
  //PHPExcel objects
  protected $phpExcelObject;
  protected $activeWorkSheet;

  //object related
  protected static $app_name = 'Clipper';

  //data related
  protected $data;

  //others
  protected static $net_promoters_cat = array('detractor', 'passive', 'promoter');

  protected static $brandt = '[brand]';
  protected $headings = array(
    "TOC" => "TABLE OF CONTENTS",
    "Table 1" => "What is the Net Promoter Score (NPS) score for my brand and my competitors?",
    "Table 2" => "How loyal are doctors to my brand?",
    "Table 3" => "How satisfied is the market?",
    "Table 4" => "Amongst doctors promoting my brand, how many other brands do they also promote?",
    "Table 5" => "Amongst my Promoters which other brands are promoted?",
    "Table 6" => "Amongst my detractors which other brands do they promote?",
    "Table 7" => "How much more of my brand do Promoters use compared to Passives and Detractors?",
    "Table 8" => "What brand messages are associated with Promoters, Passives and Detractors?",
  );
  protected $data_sequences = array(
    'Table 1' => 'NPS',
    'Table 2' => 'Loyalty',
    'Table 3' => 'DoctorsPromote',
    'Table 4' => 'PromotersPromoteMean',
    'Table 5' => 'PromotersPromote',
    'Table 6' => 'DetractorsPromote',
    'Table 7' => 'PromVsDetrPromote',
    'Table 8' => 'PPDBrandMessages',
  );
  protected static $headings_extra = "What brand messages are associated with [brand]?";
  protected static $data_sequences_extra = "PPDBrandMessagesByBrands";

  protected static $text_content = array(
    'base-all'            => 'Base: All respondents.',
    'base-who-are-aware'  => 'Base: All respondents who are aware of the brands.',
    'base-aware-of-brand' => 'Base: All respondents who are aware of [brand].',
    'caution-small-base'  => 'Caution: small base sizes in some cells.',
  );

  protected static $net_promoter_categories = array('detractor', 'passive', 'promoter');

  //styles
  protected static $toc_style = array(
    'fill' => array(
      'type'  => \PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array('rgb' => 'CCCCCC'),
    ),
    'font' => array(
      'color' => array('rgb' => 'FFFFFF'),
      'size'  => 13,
    ),
  );
  protected static $style = array(
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

  /**
   * Prepare File name & File object.
   * @method prepProcessor
   *
   * @param  string $order_id
   * @param  string $survey_type
   *
   * @return void
   */
  private function prepProcessor($order_id, $survey_type) {
    //filename
    if (empty($this->file_name)) {
      $this->file_name = explode('-', $order_id);
      $this->file_name = self::$app_name . "-Export-{$survey_type}-{$this->file_name[0]}";     
    }
    $this->file_name = $this->sanitizeFileName($this->file_name);
    
    //prep Excel object
    $this->phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();
    if (empty($this->phpExcelObject)) {
      throw new \Exception("Fail to load PHPExcel.");
    }
    $this->phpExcelObject->getProperties()->setCreator(self::$app_name)
      ->setLastModifiedBy(self::$app_name)
      ->setTitle($this->file_name);

    //just extension
    $this->file_name .= '.xls';
  }

  /**
   * Main method to process data into a file.
   * @method exportFile
   *
   * @param  PP\SampleBundle\Event\DownloadEvent $event
   *
   * @return Symfony\Component\BrowserKit\Response object of an export file
   */
  public function exportFile(DownloadEvent $event) {
    //prep file
    $order_id    = $event->getOrderId();
    $survey_type = $event->getSurveyType();
    $this->file_name = $event->getFilename();
    $this->prepProcessor($order_id, $survey_type);

    //get data
    $this->data  = $event->getRawData();

    //start writing file
    //prep headings
    $original_chartDataSequences = count($this->data_sequences);
    if (!empty($this->data['available-brands'])) {
      foreach ($this->data['available-brands'] as $brand) {
        $key = "Table " . count($this->headings);
        $this->headings[$key] = str_replace(self::$brandt, $brand, self::$headings_extra);
        $this->data_sequences[$key] = self::$data_sequences_extra;
      }
    }

    //create empty sheets
    $sheet = 0;
    foreach ($this->headings as $key => $heading) {
      if ($sheet > 0) {
        $this->phpExcelObject->createSheet($sheet);
      }
      $sheet++;
    }

    //writing data
    $sheet = 0;
    foreach ($this->headings as $key => $heading) {
      $in_sequence = FALSE;
      if (isset($this->data_sequences[$key])) {
        $in_sequence = array_search($this->data_sequences[$key], $this->data['available-charts']);
      }
      $specific_brand = FALSE;
      if (($sheet - $original_chartDataSequences) >= 1) {
        $specific_brand = ($sheet - $original_chartDataSequences) - 1;
      }
      $this->excelWriteASheet($key, $sheet, $in_sequence, $specific_brand);
      $sheet++;
    }

    //move to TOC
    $this->phpExcelObject->setActiveSheetIndex(0);

    //stream the file
    $writer = $this->container->get('phpexcel')->createWriter($this->phpExcelObject, 'Excel5');

    //stops all output:
    //notice errors / whitespace & etc which render filename bug
    //ob_end_clean();
    $response = $this->container->get('phpexcel')->createStreamedResponse($writer);

    //output
    $dispositionHeader = $response->headers->makeDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        $this->file_name
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
   * DEV tips: to quickly view by Table, fold by level 4 (case).
   *
   * @param  string $sheetname
   * @param  int $sheetnum
   * @param  boolean|int $in_sequence
   * @param  boolean|int $specific_brand
   *
   * @return void
   */
  private function excelWriteASheet($sheetname, $sheetnum, $in_sequence = FALSE, $specific_brand = FALSE)
  {
    //pre select an active sheet
    $this->activeWorkSheet = $this->phpExcelObject->setActiveSheetIndex($sheetnum);

    switch ($sheetname) {
      // TOC
      case 'TOC':
        //heading
        $this->activeWorkSheet->setCellValue('A1', $this->headings[$sheetname]);
        $this->activeWorkSheet->getStyle('A1')->applyFromArray(self::$toc_style);
        $this->activeWorkSheet->getColumnDimension('A')->setWidth(2400);

        //content
        $ind = 3;
        foreach ($this->headings as $key => $heading) {
          if ($key == $sheetname) {
            continue;
          }
          $this->activeWorkSheet->setCellValue("A{$ind}", "{$key} {$heading}");
          $this->activeWorkSheet->getCell("A{$ind}")->getHyperlink()->setUrl("sheet://'{$key}'!A1");
          $ind++;
        }
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
        if ($in_sequence === FALSE) {
          throw new \Exception("{$sheetname} has no data");
        }

        //heading
        $this->activeWorkSheet->setCellValue('A1', "{$sheetname} {$this->headings[$sheetname]}");

        //subheading
        $this->activeWorkSheet->setCellValue('A2', self::$text_content['base-who-are-aware']);

        //content
        $this->activeWorkSheet->getColumnDimension('A')->setWidth(31);
        $row = 5; //stats at
        $this->excelDrawTable($sheetname, $this->data['complete'][$in_sequence], $row, $specific_brand);
        break;

      // DoctorsPromote Chart
      case 'Table 3':
        //data prep
        if ($in_sequence === FALSE) {
          throw new \Exception("{$sheetname} has no data");
        }

        //heading
        $this->activeWorkSheet->setCellValue('A1', "{$sheetname} {$this->headings[$sheetname]}");

        //subheading
        $this->activeWorkSheet->setCellValue('A2', self::$text_content['base-who-are-aware']);

        //content
        $this->activeWorkSheet->getColumnDimension('A')->setWidth(30);
        $this->activeWorkSheet->getColumnDimension('B')->setWidth(30);
        $row = 5; //stats at
        $this->excelDrawTable($sheetname, $this->data['complete'][$in_sequence], $row, $specific_brand);
        break;

      // PPDBrandMessages Chart
      case 'Table 8':
      // PPDBrandMessagesByBrands Chart
      case ($specific_brand !== FALSE): //Table 9 to any number of brands
        //data prep
        if ($in_sequence === FALSE) {
          throw new \Exception("{$sheetname} has no data");
        }

        //heading
        $this->activeWorkSheet->setCellValue('A1', "{$sheetname} {$this->headings[$sheetname]}");

        //subheading
        if ($specific_brand === FALSE) { // Table 8
          $this->activeWorkSheet->setCellValue('A2', self::$text_content['base-all']);
        }
        else {
          $text = str_replace(self::$brandt, $this->data['available-brands'][$specific_brand], self::$text_content['base-aware-of-brand']);
          $this->activeWorkSheet->setCellValue('A2', $text);
        }

        //content
        $this->activeWorkSheet->getColumnDimension('A')->setWidth(38);
        $row = 5; //stats at
        $this->excelDrawTable($sheetname, $this->data['complete'][$in_sequence], $row, $specific_brand);
        break;
    } //switch $sheetname
    
    if ($sheetname != 'TOC') {
      //drilldown - single: CLIP-69
      foreach ($this->data['filtered'] as $type => $filters) {
        foreach ($filters as $filter => $filtered_data) {
          $row += 3; //space between charts
          $drilldownHeading = ucwords($type);
          $this->excelDrawTable($sheetname, $filtered_data[$in_sequence], $row, $specific_brand, "Drilldown {$drilldownHeading}: {$filter}");
        }
      }
      //drilldown - dual: CLIP-69:v2
      foreach ($this->data['combined-filtered'] as $combined_filter) {
        $row += 3; //space between charts
        $drilldownHeading = "Drilldown: {$combined_filter['filters']['country']} and {$combined_filter['filters']['specialty']}";
        $this->excelDrawTable($sheetname, $combined_filter['data'][$in_sequence], $row, $specific_brand, $drilldownHeading);
      }
    }
    //styling
    $this->activeWorkSheet->getStyle("A1")->getFont()->setBold(TRUE);

    //set sheet name
    $this->activeWorkSheet->setTitle($sheetname);
    //reset cursor
    $this->activeWorkSheet->setSelectedCells('A1');
  }

  /**
   * Helper method to draw specific table
   * @method excelDrawTable
   *
   * DEV tips: to quickly view by Table, fold by level 4 (case).
   *
   * @param  string $sheetname
   * @param  array $dataTable
   * @param  integer &$row
   * @param  boolean|int $specific_brand
   * @param  boolean|string $drilldown
   *    Flag | Label to identify if table meant for drilldown.
   *
   * @return void
   */
  private function excelDrawTable($sheetname, $dataTable = array(), &$row = 4, $specific_brand = FALSE, $drilldown = FALSE)
  {
    $col = range('A', 'Z');

    if ($drilldown !== FALSE) {
      //drilldown heading first
      $this->activeWorkSheet->setCellValue("A{$row}", "{$drilldown}");
      $this->activeWorkSheet->getStyle("A{$row}")->getFont()->setBold(TRUE);
      $row++;
    }
    
    if (empty($dataTable['datatable'])) {
      $this->activeWorkSheet->setCellValue("A{$row}", "No data.");
      $row++;
      return;
    }
    
    //draw table
    switch ($sheetname) {
      // NPS Chart
      case 'Table 1':
        //prep data
        $localDataTable = array();
        array_walk($dataTable['datatable'], function($set, $key) use (&$localDataTable) {
          // new requirement since FirstView: hide brand with no base (hidden)
          if ((empty($set['brand'])) || ($set['base'] == 0)) {
            return; // array_walk
          }
          $localDataTable[$set['brand']] = $set;
        });        
        if (empty($localDataTable)) {
          break; // switch
        }
        // new requirement since FirstView: order by mean
        uasort($localDataTable, function ($a, $b) {
          if ($a['score'] == $b['score']) {
            return 0;
          }
          return (($a['score'] > $b['score']) ? -1 : 1);
        });
        
        $rowStarts = $row;
        
        // brand list
        $colStarts = $alp = 1;
        foreach($localDataTable as $brand => $set) {
          $this->activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$set['brand']}");
          $colEnds = $alp;
          $alp++;
        }
        $row++;

        //base
        $this->activeWorkSheet->setCellValue("A{$row}", "Base");
        $alp = 1;
        foreach($localDataTable as $brand => $set) {
          $this->activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$set['base']}");
          $alp++;
        }
        $row++;
        $row++;

        //net
        foreach (self::$net_promoter_categories as $type) {
          $type .= 's';
          $cattype = ucwords($type);
          $this->activeWorkSheet->setCellValue("A{$row}", $cattype);
          $alp = 1;
          foreach($localDataTable as $brand => $set) {
            $this->activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$set[$type]}%");
            $alp++;
          }
          $row++;
        }
        $row++;

        //mean
        $this->activeWorkSheet->setCellValue("A{$row}", "Mean scores");

        $alp = 1;
        foreach($localDataTable as $brand => $set) {
          $this->activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$set['score']}");
          $alp++;
        }

        $end_row = $row;

        //styling
        $this->activeWorkSheet->getStyle("{$col[$colStarts]}{$rowStarts}:{$col[$colEnds]}{$end_row}")->applyFromArray(self::$style['align-center']);
        $this->activeWorkSheet->getStyle("{$col[$colStarts]}{$rowStarts}:{$col[$colEnds]}{$rowStarts}")->getFont()->setBold(TRUE);
        $this->activeWorkSheet->getStyle("{$col[$colStarts]}{$rowStarts}:{$col[$colEnds]}{$rowStarts}")->getAlignment()->setWrapText(TRUE);
        $this->activeWorkSheet->getRowDimension($rowStarts)->setRowHeight(80);
        foreach (range($colStarts, $colEnds) as $cl) {
          $this->activeWorkSheet->getColumnDimension("{$col[$cl]}")->setWidth(15);          
        }
        $this->activeWorkSheet->getStyle("A{$end_row}:{$col[$colEnds]}{$end_row}")->getFont()->setBold(TRUE);
        $styles = array('borders' => array('bottom' => self::$style['def-border']));
        $this->activeWorkSheet->getStyle("A{$rowStarts}:{$col[$colEnds]}{$rowStarts}")->applyFromArray($styles);
        $styles = array('borders' => array('right' => self::$style['def-border']));
        $this->activeWorkSheet->getStyle("A{$rowStarts}:A{$end_row}")->applyFromArray($styles);
        break;

      // Loyalty Chart
      case 'Table 2':
        // prep data
        $localDataTable = array();
        array_walk($dataTable['datatable']['brands'], function($set, $key) use (&$localDataTable) {
          // new requirement since FirstView: hide brand with no base (hidden)
          if (empty($set['base'])) {
            return; // array_walk
          }
          $localDataTable[$set['brand']] = $set;
        });        
        $localDataTable['Mean - all brands'] = array(
          'brand' => 'Mean - all brands',
          'base'    => $dataTable['datatable']['base'],
          'loyalty' => $dataTable['datatable']['mean'],
        );

        $rowStarts = $row;

        //heading
        $this->activeWorkSheet->setCellValue("B{$row}", "Base");
        $this->activeWorkSheet->setCellValue("C{$row}", "Mean");
        $row++;

        // new requirement since FirstView: order by mean
        uasort($localDataTable, function ($a, $b) {
          return (strcmp($a['loyalty'], $b['loyalty']) < 0);
        });
        foreach ($localDataTable as $brand => $meandat) {          
          $this->activeWorkSheet->setCellValue("A{$row}", "{$meandat['brand']}");
          $this->activeWorkSheet->setCellValue("B{$row}", "{$meandat['base']}");
          $this->activeWorkSheet->setCellValue("C{$row}", "{$meandat['loyalty']}");
          $row++;
        }

        $end_row = $row;

        //styling
        $styles = array('borders' => array('bottom' => self::$style['def-border']));
        $this->activeWorkSheet->getStyle("A{$rowStarts}:C{$rowStarts}")->applyFromArray($styles);
        $styles = array('borders' => array('left' => self::$style['def-border']), 'alignment' => self::$style['align-center']['alignment']);
        $this->activeWorkSheet->getStyle("B{$rowStarts}:B{$end_row}")->applyFromArray($styles);
        $this->activeWorkSheet->getStyle("C{$rowStarts}:C{$end_row}")->applyFromArray($styles);
        break;

      // DoctorsPromote Chart
      case 'Table 3':
        $rowStarts = $row;

        //heading
        $this->activeWorkSheet->setCellValue("B{$row}", "Number of brands promoted");
        $row++;

        //content
        $this->activeWorkSheet->setCellValue("A{$row}", "Base");
        $this->activeWorkSheet->setCellValue("B{$row}", $dataTable['datatable']['base']);
        $row++;

        $this->activeWorkSheet->setCellValue("A{$row}", "Non Promoters \r\n(no brands promoted)");
        $this->activeWorkSheet->setCellValue("B{$row}", "{$dataTable['datatable']['dissatisfied']['amount']}%");
        $row++;

        $this->activeWorkSheet->setCellValue("A{$row}", "Promoters\r\n(at least one brand)");
        $this->activeWorkSheet->setCellValue("B{$row}", "{$dataTable['datatable']['satisfied']['amount']}%");
        $row++;

        $this->activeWorkSheet->setCellValue("A{$row}", "Exclusive \r\n(only one brand promoted)");
        $this->activeWorkSheet->setCellValue("B{$row}", "{$dataTable['datatable']['satisfied']['exclusive']['amount']}%");
        $row++;

        $this->activeWorkSheet->setCellValue("A{$row}", "Shared \r\n(more than one brand promoted)");
        $this->activeWorkSheet->setCellValue("B{$row}", "{$dataTable['datatable']['satisfied']['shared']['amount']}%");

        $end_row = $row;

        //styling
        $styles = array('borders' => array('right' => self::$style['def-border']));
        $this->activeWorkSheet->getStyle("A{$rowStarts}:A{$end_row}")->applyFromArray($styles);
        $styles = array('borders' => array('bottom' => self::$style['def-border']));
        $this->activeWorkSheet->getStyle("A{$rowStarts}:B{$rowStarts}")->applyFromArray($styles);
        $this->activeWorkSheet->getStyle("B{$rowStarts}:B{$end_row}")->applyFromArray(self::$style['align-center']);
        $this->activeWorkSheet->getStyle("B{$rowStarts}:B{$end_row}")->getAlignment()->setWrapText(TRUE);
        foreach (range(($rowStarts + 2), $end_row) as $rw) {
          $this->activeWorkSheet->getRowDimension($rw)->setRowHeight(35);
        }
        break;

      // PromotersPromoteMean Chart
      case 'Table 4':
        $rowStarts = $row;

        //heading
        $this->activeWorkSheet->setCellValue("B{$row}", "Base");
        $this->activeWorkSheet->setCellValue("C{$row}", "Mean");
        $row++;
        
        $dataTable['datatable']['brands']['Mean - all brands'] = array(
          'base'  => $dataTable['datatable']['overall']['base'],
          'mean'  => $dataTable['datatable']['overall']['mean'],          
        );
        
        // new requirement since FirstView: order by mean
        uasort($dataTable['datatable']['brands'], function ($a, $b) {
          return (strcmp($a['mean'], $b['mean']) < 0);
        });

        //brands
        foreach($dataTable['datatable']['brands'] as $brand => $set) {
          // new requirement since FirstView: hide brand with no base (hidden)
          if (empty($set['base'])) {
            continue;
          }
          $this->activeWorkSheet->setCellValue("A{$row}", "{$brand}");
          $this->activeWorkSheet->setCellValue("B{$row}", "{$set['base']}");
          $this->activeWorkSheet->setCellValue("C{$row}", "{$set['mean']}");
          $end_row = $row;
          $row++;
        }        

        //styling
        $styles = array('borders' => array('bottom' => self::$style['def-border']));
        $this->activeWorkSheet->getStyle("A{$rowStarts}:C{$rowStarts}")->applyFromArray($styles);
        $styles = array('borders' => array('left' => self::$style['def-border']), 'alignment' => self::$style['align-center']['alignment']);
        $this->activeWorkSheet->getStyle("B{$rowStarts}:B{$end_row}")->applyFromArray($styles);
        $this->activeWorkSheet->getStyle("C{$rowStarts}:C{$end_row}")->applyFromArray($styles);
        break;

      // PromotersPromote Chart
      case 'Table 5':
      // DetractorsPromote Chart
      case 'Table 6':
        //prep data
        $localDataTable = array();
        array_walk($dataTable['datatable'], function($set, $key) use (&$localDataTable) {
          // new requirement since FirstView: hide brand with no base (hidden)
          if (empty($set['base'])) {
            return;
          }
          $localDataTable[$set['brand']] = $set;
        });

        $rowStarts = $row;

        // brand list
        $colStarts = $colEnds = $alp = 1;
        foreach($dataTable['brands'] as $brand) {
          // new requirement since FirstView: hide brand with no base (hidden)
          if (!isset($localDataTable[$brand])) {
            continue;
          }
          $this->activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$brand}");
          $colEnds = $alp;
          $alp++;
        }
        $row++;

        //base
        $this->activeWorkSheet->setCellValue("A{$row}", "Base");
        $alp = 1;
        foreach($dataTable['brands'] as $brand) {
          // new requirement since FirstView: hide brand with no base (hidden)
          if (!isset($localDataTable[$brand])) {
            continue;
          }
          $this->activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$localDataTable[$brand]['base']}");
          $alp++;
        }
        $row++;

        //brands
        foreach($dataTable['brands'] as $brand) {
          // new requirement since FirstView: hide brand with no base (hidden)
          if (!isset($localDataTable[$brand])) {
            continue;
          }
          $this->activeWorkSheet->setCellValue("A{$row}", "{$brand}");
          $alp = 0;
          foreach($dataTable['brands'] as $compbrand) {
            // new requirement since FirstView: hide brand with no base (hidden)
            if (!isset($localDataTable[$compbrand])) {
              continue;
            }
            $alp++;
            if ($brand == $compbrand) {
              $this->activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "--");
              continue;
            }
            $prec = 0;
            if ((!is_object($localDataTable[$compbrand]['competitors'])) && (!empty($localDataTable[$compbrand]['competitors'][$brand]))) {
              $prec = $localDataTable[$compbrand]['competitors'][$brand];
            }
            $this->activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$prec}%");
          }
          $row++;
        }

        $end_row = ($row - 1);

        //styling
        $this->activeWorkSheet->getStyle("{$col[$colStarts]}{$rowStarts}:{$col[$colEnds]}{$end_row}")->applyFromArray(self::$style['align-center']);
        $this->activeWorkSheet->getStyle("{$col[$colStarts]}{$rowStarts}:{$col[$colEnds]}{$rowStarts}")->getFont()->setBold(TRUE);
        $this->activeWorkSheet->getStyle("{$col[$colStarts]}{$rowStarts}:{$col[$colEnds]}{$rowStarts}")->getAlignment()->setWrapText(TRUE);
        $this->activeWorkSheet->getRowDimension($rowStarts)->setRowHeight(80);
        foreach (range($colStarts, $colEnds) as $cl) {
          $this->activeWorkSheet->getColumnDimension("{$col[$cl]}")->setWidth(15);          
        }
        $styles = array('borders' => array('bottom' => self::$style['def-border']));
        $this->activeWorkSheet->getStyle("A{$rowStarts}:{$col[$colEnds]}{$rowStarts}")->applyFromArray($styles);
        $styles = array('borders' => array('right' => self::$style['def-border']));
        $this->activeWorkSheet->getStyle("A{$rowStarts}:A{$end_row}")->applyFromArray($styles);
        break;

      // PromVsDetrPromote Chart
      case 'Table 7':
        //prep data
        $localDataTable = array();
        array_walk($dataTable['datatable'], function($set, $key) use (&$localDataTable) {
          // new requirement since FirstView: hide brand with no base (hidden)
          if (
            (empty($set['promoters_count'])) &&
            (empty($set['passives_count'])) &&
            (empty($set['detractors_count'])) 
          ) {
            return;
          }
          $localDataTable[$set['brand']] = $set;
        });

        $rowStarts = $row;
        $colStarts = 1;
        $uppur_border = array();

        //heading 1
        $alp = (count(self::$net_promoter_categories));
        $this->activeWorkSheet->mergeCells("{$col[$colStarts]}{$row}:{$col[$alp]}{$row}");
        $this->activeWorkSheet->setCellValue("{$col[$colStarts]}{$row}", "Market share");
        $row++;

        //heading 2
        $alp = 1;
        foreach (self::$net_promoter_categories as $cat) {
          $cat = ucwords($cat) . 's';
          $this->activeWorkSheet->setCellValue("{$col[$alp]}{$row}", $cat);
          $this->activeWorkSheet->getColumnDimension("{$col[$alp]}")->setWidth(12);
          $colEnds = $alp;
          $alp++;
        }
        $row++;

        //brands
        foreach ($dataTable['brands'] as $brand) {
          // new requirement since FirstView: hide brand with no base (hidden)
          if (!isset($localDataTable[$brand])) {
            continue;
          }
          $uppur_border[] = $row;

          //base
          $this->activeWorkSheet->setCellValue("A{$row}", "Base");
          $alp = 1;
          foreach (self::$net_promoter_categories as $cat) {
            $cat = $cat . 's_count';
            $this->activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$localDataTable[$brand][$cat]}");
            $alp++;
          }
          $row++;

          //perc
          $this->activeWorkSheet->setCellValue("A{$row}", "{$brand}");
          $alp = 1;
          foreach (self::$net_promoter_categories as $cat) {
            $cat = $cat . 's';
            $this->activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$localDataTable[$brand][$cat]}%");
            $alp++;
          }
          $row++;
          $row++;
        }

        //footer
        $this->activeWorkSheet->setCellValue("A{$row}", self::$text_content['caution-small-base']);
        $this->activeWorkSheet->getStyle("A{$row}")->applyFromArray(self::$style['def-note']);

        $end_row = ($row - 1);

        //styling
        $this->activeWorkSheet->getStyle("{$col[$colStarts]}{$rowStarts}:{$col[$colEnds]}{$end_row}")->applyFromArray(self::$style['align-center']);
        $styles = array('borders' => array('right' => self::$style['def-border']));
        $this->activeWorkSheet->getStyle("A{$rowStarts}:A{$end_row}")->applyFromArray($styles);
        if (!empty($uppur_border)) {
          $styles = array('borders' => array('top' => self::$style['def-border']));
          foreach ($uppur_border as $row_ind) {
            $this->activeWorkSheet->getStyle("A{$row_ind}:{$col[$colEnds]}{$row_ind}")->applyFromArray($styles);
          }
        }
        break;

      // PPDBrandMessages Chart
      case 'Table 8':
        if (empty($dataTable['datatable'])) {
          $this->activeWorkSheet->setCellValue("A{$row}", "No result.");
          return;
        }
        $rowStarts = $row;
        $colStarts = 1;
        $uppur_border = array();

        //heading 1
        $alp = (count(self::$net_promoter_categories));
        $this->activeWorkSheet->mergeCells("{$col[$colStarts]}{$row}:{$col[$alp]}{$row}");
        $this->activeWorkSheet->setCellValue("{$col[$colStarts]}{$row}", "All Brands");
        $row++;
        
        $base_row = $row;
        
        //heading 2
        $alp = 1;
        foreach (self::$net_promoter_categories as $cat) {
          $cat = ucwords($cat) . 's';
          $this->activeWorkSheet->setCellValue("{$col[$alp]}{$row}", $cat);
          $this->activeWorkSheet->getColumnDimension("{$col[$alp]}")->setWidth(12);
          $colEnds = $alp;
          $alp++;
        }
        $row++;

        //messages
        foreach ($dataTable['datatable'] as $msgInd => $mesge) {
          $this->activeWorkSheet->setCellValue("A{$row}", "{$mesge['message']}");
          $alp = 1;
          foreach (self::$net_promoter_categories as $cat) {            
            //perc
            $perc = $cat . 's';
            $this->activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$mesge[$perc]}%");
            $alp++;
          }
          $row++;
        }
        $row--;

        $end_row = $row;

        //styling
        $this->activeWorkSheet->getStyle("{$col[$colStarts]}{$rowStarts}:{$col[$colEnds]}{$end_row}")->applyFromArray(self::$style['align-center']);
        $styles = array('borders' => array('right' => self::$style['def-border']));
        $this->activeWorkSheet->getStyle("A{$rowStarts}:A{$end_row}")->applyFromArray($styles);
        $styles = array('borders' => array('bottom' => self::$style['def-border']));
        $this->activeWorkSheet->getStyle("A{$base_row}:{$col[$colEnds]}{$base_row}")->applyFromArray($styles);
        break;

      // PPDBrandMessagesByBrands Chart
      case ($specific_brand !== FALSE): //Table 9 ~ to any number of brands
        $rowStarts = $row;
        $colStarts = 1;
        $uppur_border = array();

        //heading 1
        $alp = (count(self::$net_promoter_categories));
        $this->activeWorkSheet->mergeCells("{$col[$colStarts]}{$row}:{$col[$alp]}{$row}");
        $this->activeWorkSheet->setCellValue("{$col[$colStarts]}{$row}", "{$dataTable['brands'][$specific_brand]}");
        $row++;

        //heading 2
        $alp = 1;
        foreach (self::$net_promoter_categories as $cat) {
          $cat = ucwords($cat) . 's';
          $this->activeWorkSheet->setCellValue("{$col[$alp]}{$row}", $cat);
          $this->activeWorkSheet->getColumnDimension("{$col[$alp]}")->setWidth(12);
          $colEnds = $alp;
          $alp++;
        }
        $row++;

        //base
        $base_row = $row;
        $this->activeWorkSheet->setCellValue("A{$row}", "Base");
        $alp = 1;
        $brand = $dataTable['brands'][$specific_brand];
        $brandset = end($dataTable['datatable']['brands'][$brand]);
        foreach (self::$net_promoter_categories as $cat) {
          $this->activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$brandset[$cat]['base']}");
          $alp++;
        }
        $row++;

        // brand questions
        $brand = $dataTable['brands'][$specific_brand];
        $scores = $dataTable['datatable']['brands'][$brand];
        foreach ($scores as $scindx => $score) {
          $this->activeWorkSheet->setCellValue("A{$row}", "{$score['question']}");
          $alp = 1;
          foreach (self::$net_promoter_categories as $cat) {
            $this->activeWorkSheet->setCellValue("{$col[$alp]}{$row}", "{$score[$cat]['perc']}%");
            $alp++;
          }
          $row++;
        }

        //footer
        $this->activeWorkSheet->setCellValue("A{$row}", self::$text_content['caution-small-base']);
        $this->activeWorkSheet->getStyle("A{$row}")->applyFromArray(self::$style['def-note']);
        $row--;

        $end_row = $row;        

        //styling
        $this->activeWorkSheet->getStyle("{$col[$colStarts]}{$rowStarts}:{$col[$colEnds]}{$end_row}")->applyFromArray(self::$style['align-center']);
        $styles = array('borders' => array('right' => self::$style['def-border']));
        $this->activeWorkSheet->getStyle("A{$rowStarts}:A{$end_row}")->applyFromArray($styles);
        $styles = array('borders' => array('bottom' => self::$style['def-border']));
        $this->activeWorkSheet->getStyle("A{$base_row}:{$col[$colEnds]}{$base_row}")->applyFromArray($styles);
        $base_row--;
        $this->activeWorkSheet->getStyle("A{$base_row}:{$col[$colEnds]}{$base_row}")->applyFromArray($styles);
        break;
    } //switch $sheetname

    $row++;
  }
}
