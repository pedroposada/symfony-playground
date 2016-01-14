<?php

/**
 * file: PSL\ClipperBundle\Charts\Pdf\Types\NpsPlusPdf.php
 * service: clipper.charts.pdf.nps_plus
 * 
 * Create reports in pdf format for NPS Plus surveys
 **/

namespace PSL\ClipperBundle\Charts\Pdf\Types;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Entity\FirstQGroup;

class NpsPlusPdf
{
  protected $container;
  protected $em;
  protected $logger;
  protected $survey_type;  
  protected $templating;
  protected $fqg;
  protected $geoMapper;
  
  /**
   * @param ContainerInterface $container
   * @param string $survey_type
   */
  public function __construct(ContainerInterface $container, $survey_type) 
  {
    $this->container        = $container;
    $this->em               = $container->get('doctrine')->getManager();
    $this->logger           = $container->get('monolog.logger.clipper');
    $this->templating       = $container->get('templating');
    $this->geoMapper        = $container->get('geo_mapper');
    $this->chart_helper     = $container->get('chart_helper');
    $this->survey_type      = $survey_type;
  }
  
  /**
   * @param ChartEvent $event
   * @param string $eventName
   * @param EventDispatcherInterface $dispatcher
   */
  public function onPdf(ChartEvent $event, $eventName, EventDispatcherInterface $dispatcher)
  {
    // query order id
    $this->fqg = $this->em->getReference('PSLClipperBundle:FirstQGroup', $event->getOrderId());
    // check for type
    if (current($this->fqg->getFormDataByField('survey_type')) === $this->survey_type) {
      $this->main($event);
    }
  }
  
  /**
   * @param ChartEvent $event ChartEvent
   */
  protected function main(ChartEvent $event)
  {
    // Unique building algorithm for NPS+ reports
    
    // get template maps segmented by regions
    $maps = new ArrayCollection();
    $regions = $this->geoMapper->findRegionsByMarkets($this->fqg->getFormDataByField('markets'));

    foreach ($regions as $region) {
      $maps->add($this->getTemplateMap($this->fqg, array('region' => $region)));
    }

    // set pages in event object
    $event->setPdfMaps($maps);

    if ($event->getName() === 'chart_pdf') {
      // get htmls
      $htmls = $this->getHtmls($maps);
      $event->setHtmlFiles($htmls);

      // get pdfs
      $pdfs = $this->getPdfs($htmls);
      $event->setPdfFiles($pdfs);

      // remove htmls
      $this->deleteHtmls($htmls);
    }
  }

  /**
   * Deletes the temp. generated HTMLs
   * 
   * @param ArrayCollection $htmls An ArrayCollection of ArrayCollections
   * of the files generated in this form:
   * array(
   *   array(
   *     'path/to/file/myTemphtml12345.html',
   *     'path/to/file/myTemphtml67890.html',
   *     'path/to/file/myTemphtmlABCDE.html',
   *       ...
   *   )
   * )
   */
  protected function deleteHtmls($htmls)
  {
    foreach ($htmls as $document) {
      foreach ($document as $file) {
        unlink($file);
      }
    }
  }

  /**
   * Takes the template maps, renders the templates and creates temporary htmls.
   * It returns an array of arrays of the files created.
   * 
   * @param ArrayCollection $templateMaps The template maps as 
   *   getTemplateMap returns.
   * 
   * @return ArrayCollection ArrayCollection of ArrayCollections of
   *   the files created.
   */
  protected function getHtmls($templateMaps)
  {
    $htmls = new ArrayCollection();
    
    foreach ($templateMaps as $mapIdx => $map) {
      $pages = new ArrayCollection();
      foreach ($map as $mapDataIdx => $mapData) {
        $tpl = $mapData['twig'];
        $plc = isset($mapData['placeholders']) ? $mapData['placeholders'] : array();
        $file = $this->saveTempHtml($tpl, $plc);
        $pages->add($file);
      }
      $htmls->add($pages);
      unset($pages);
    }

    return $htmls;
  }

  /**
   * Takes a template and its placeholders, renders it and creates a temp HTML
   * with it.
   * 
   * @param String $template Template resource location.
   * @param Array $placeholder All the placeholders for the twig template.
   * 
   * @return String The temp HTML file created. 
   */
  protected function saveTempHtml($template, $placeholder=array())
  {
    $fname = uniqid('clipper_NpsPlus', true) . '.html';
    $html = $this->templating->render($template, $placeholder);
    $tmpFile = $this->container->get('kernel')->getRootDir() . '/../web/bundles/pslclipper/html/' . $fname;
    file_put_contents($tmpFile, $html);
    return 'file://'.$tmpFile;
  }
  
  /**
   * @param ArrayCollection $templateMaps
   * 
   * @return ArrayCollection $pdfs
   **/
  protected function getPdfs(ArrayCollection $htmls)
  {
    // create collection of pages
    $pdfs = new ArrayCollection();

    foreach ($htmls as $htmlIdx => $htmlList) {
      // render pages into PDF files
      $hash = uniqid();
      $filepath = $this->container->get('kernel')->getRootDir() . '/../web/bundles/pslclipper/pdf/' . $hash . '.pdf';

      $pdfGenerator = $this->container->get('knp_snappy.pdf');
      $pdfGenerator->getInternalGenerator()->setTimeout(500);
      $pdfGenerator->generate($htmlList->toArray(), $filepath, array(
        'encoding' => 'utf-8',
        'lowquality' => false,
        'enable-smart-shrinking' => true,
        'images' => true,
        'page-size' => 'Letter',
        'dpi' => 300,
        'javascript-delay' => 15000,
        'enable-javascript' => true,
        'no-stop-slow-scripts' => true,
        'debug-javascript' => true,
        'footer-center' => '[page]'
      ), true); // headless browser

      $pdfs->add($filepath);
    }
    
    return $pdfs;
  }

  /**
   * @param \PSL\ClipperBundle\Entity\FirstQGroup $fqg
   * 
   * @return ArrayCollection $templateMap
   **/
  protected function getTemplateMap(FirstQGroup $fqg, $drilldown=array('region'=>'none'))
  {
    $map = new ArrayCollection();
    $formData = $this->fqg->getFormDataUnserialized();
    $countries = $this->geoMapper->getCountries($drilldown['region']);

    // Introduction
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/introduction.html.twig',
      'placeholders' => array(
        'main_title' => $formData['title'],
        'region' => $drilldown['region'],
        'countries' => $countries,
        'specialties' => $formData['specialties'],
        'patient_type' => $formData['patient_type'],
        'participants' => $formData['num_participants'],
        'launch_date' => $formData['launch_date'],
        'completion_date' => $formData['completion_date']
      )
    ));

    // Table of contents
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/tableofcontents.html.twig'
    ));
    // Get data
    $filters = array(
      'countries' => $countries
    );
    $data = $this->chart_helper->getDataStructure($fqg->getId(), $filters);

    // Chart 1
    $datatable = $this->getChartDataStructuresByMachineName($data, 'NPS')['datatable'];
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/chart01.html.twig',
      'placeholders' => array(
        'chart_datatable' => json_encode($datatable)
      )
    ));
    unset($datatable);
    // Chart 2
    $datatable = $this->getChartDataStructuresByMachineName($data, 'Loyalty')['datatable'];
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/chart02.html.twig',
      'placeholders' => array(
        'chart_datatable' => json_encode($datatable)
      )
    ));
    unset($datatable);
    // Chart 3
    $datatable = $this->getChartDataStructuresByMachineName($data, 'DoctorsPromote')['datatable'];
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/chart03.html.twig',
      'placeholders' => array(
        'chart_datatable' => json_encode($datatable)
      )
    ));
    unset($datatable);
    // Chart 4
    $datatable = $this->getChartDataStructuresByMachineName($data, 'PromotersPromoteMean')['datatable'];
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/chart04.html.twig',
      'placeholders' => array(
        'chart_datatable' => json_encode($datatable)
      )
    ));
    unset($datatable);
    // Chart 5
    $datatable = $this->getChartDataStructuresByMachineName($data, 'PromotersPromote')['datatable'];
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/chart05.html.twig',
      'placeholders' => array(
        'chart_datatable' => json_encode($datatable)
      )
    ));
    unset($datatable);
    // Chart 6
    $datatable = $this->getChartDataStructuresByMachineName($data, 'DetractorsPromote')['datatable'];
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/chart06.html.twig',
      'placeholders' => array(
        'chart_datatable' => json_encode($datatable)
      )
    ));
    unset($datatable);
    // Chart 7
    $datatable = $this->getChartDataStructuresByMachineName($data, 'PromVsDetrPromote')['datatable'];
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/chart07.html.twig',
      'placeholders' => array(
        'chart_datatable' => json_encode($datatable)
      )
    ));
    unset($datatable);
    //return $map;

    // Chart 8
    // // - Intro
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/chart08intro.html.twig',
      'placeholders' => array(
        'region' => $drilldown['region']
      )
    ));
    // - Charts
    foreach ($formData['brands'] as $brandIdx => $brand) {
      // Get subdata
      $filters = array(
        'countries' => $countries,
        'brand' => $brand
      );
      $subdata = $this->chart_helper->getDataStructure($fqg->getId(), $filters);
      $datatable = $this->getChartDataStructuresByMachineName($subdata, 'PPDBrandMessages')['datatable'];
      $map->add(array(
        'twig' => 'PSLClipperBundle:Charts:nps_plus/chart08.html.twig',
        'placeholders' => array(
          'chart_datatable' => json_encode($datatable),
          'subsection_number' => $brandIdx + 1,
          'brand' => $brand
        )
      ));
      unset($datatable);
      unset($subdata);
    }
    //return $map;

    // Chart 9
    // - Intro
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/chart09intro.html.twig'
    ));
    // - Charts
    $brandCount = count($formData['brands']);
    foreach ($countries as $marketIdx => $market) {
      // Get subdata
      $filters = array(
        'countries' => $market
      );
      $subdata = $this->chart_helper->getDataStructure($fqg->getId(), $filters);
      $datatable = $this->getChartDataStructuresByMachineName($subdata, 'DNA')['datatable'];

      foreach ($datatable as $brandIdx => $brand_datatable) {
        $map->add(array(
          'twig' => 'PSLClipperBundle:Charts:nps_plus/chart09.html.twig',
          'placeholders' => array(
            'chart_datatable' => json_encode([$brand_datatable]),
            'subsection_number' => ($marketIdx * $brandCount) + ($brandIdx + 1),
            'brand' => $brand_datatable['brand'],
            'country' => $market
          )
        ));
      }

      unset($brand_datatable);
      unset($datatable);
      unset($subdata);
    }

    // Appendix
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/appendix.html.twig'
    ));
    
    return $map;
  }

  /**
   * Searches in the data structure providen for the substructure with the
   * name $machinename.
   * @param ArrayCollection $dataStructure Main data structure as given by
   *   the chart builder
   * @param String $machinename A machine name to look for.
   * 
   * @return ArrayCollection The substructure found.
   */
  private function getChartDataStructuresByMachineName($dataStructure, $machinename)
  {
    if (!isset($dataStructure['charts'])) {
      throw new Exception('Invalid data structure given.');
    }
    $charts = $dataStructure['charts']->toArray();
    foreach ($charts as $chart) {
      if ($chart['chartmachinename'] === $machinename) {
        return $chart;
      }
    }
    throw new Exception('Chart data structure not found');
  }
}