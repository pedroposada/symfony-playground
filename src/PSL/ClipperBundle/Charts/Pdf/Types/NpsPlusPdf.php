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

    // get pdfs
    $pdfs = $this->getPdfs($maps);

    // set pages in event object
    $event->setPdfMaps($maps);
    $event->setPdfFiles($pdfs);
  }
  
  /**
   * @param ArrayCollection $templateMaps
   * 
   * @return ArrayCollection $pdfs
   **/
  protected function getPdfs(ArrayCollection $templateMaps)
  {
    // create collection of pages
    $pdfs = new ArrayCollection();
    
    foreach ($templateMaps as $mapIdx => $map) {
      $pages = new ArrayCollection();
      
      foreach ($map as $mapDataIdx => $mapData) {
        $plc = isset($mapData['placeholders']) ? $mapData['placeholders'] : array();
        $html = $this->templating->render($mapData['twig'], $plc);
        $pages->add($html);
      }
      // render pages into PDF files
      $hash = uniqid();
      $filepath = $this->container->get('kernel')->getRootDir() . '/../web/bundles/pslclipper/pdf/' . $hash . '.pdf';
      $this->container->get('knp_snappy.pdf')->generateFromHtml($pages->toArray(), $filepath); // headless browser
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

    // Introduction
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/introduction.html.twig',
      'placeholders' => array(
        'main_title' => $formData['title'],
        'region' => $drilldown['region']
      )
    ));
    // Table of contents
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/tableofcontents.html.twig'
    ));
    // Get data
    $countries = $this->geoMapper->getCountries($drilldown['region']);
    $filters = array(
      'countries' => $countries
    );
    $data = $this->chart_helper->getDataStructure($fqg->getId(), $filters);

    // Chart 1
    $datatable = json_encode($this->getChartDataStructuresByMachineName($data, 'NPS')['datatable']);
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/chart01.html.twig',
      'placeholders' => array(
        'chart_datatable' => json_encode($datatable)
      )
    ));
    // Chart 2
    $datatable = json_encode($this->getChartDataStructuresByMachineName($data, 'Loyalty')['datatable']);
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/chart02.html.twig',
      'placeholders' => array(
        'chart_datatable' => json_encode($datatable)
      )
    ));
    // Chart 3
    $datatable = json_encode($this->getChartDataStructuresByMachineName($data, 'DoctorsPromote')['datatable']);
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/chart03.html.twig',
      'placeholders' => array(
        'chart_datatable' => json_encode($datatable)
      )
    ));
    // Chart 4
    $datatable = json_encode($this->getChartDataStructuresByMachineName($data, 'PromotersPromoteMean')['datatable']);
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/chart04.html.twig',
      'placeholders' => array(
        'chart_datatable' => json_encode($datatable)
      )
    ));
    // Chart 5
    $datatable = json_encode($this->getChartDataStructuresByMachineName($data, 'PromotersPromote')['datatable']);
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/chart05.html.twig',
      'placeholders' => array(
        'chart_datatable' => json_encode($datatable)
      )
    ));
    // Chart 6
    $datatable = json_encode($this->getChartDataStructuresByMachineName($data, 'DetractorsPromote')['datatable']);
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/chart06.html.twig',
      'placeholders' => array(
        'chart_datatable' => json_encode($datatable)
      )
    ));
    // Chart 7
    $datatable = json_encode($this->getChartDataStructuresByMachineName($data, 'PromVsDetrPromote')['datatable']);
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/chart07.html.twig',
      'placeholders' => array(
        'chart_datatable' => json_encode($datatable)
      )
    ));

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
      $datatable = json_encode($this->getChartDataStructuresByMachineName($subdata, 'PPDBrandMessages')['datatable']);
      $map->add(array(
        'twig' => 'PSLClipperBundle:Charts:nps_plus/chart08.html.twig',
        'placeholders' => array(
          'chart_datatable' => json_encode($datatable),
          'subsection_number' => $brandIdx + 1,
          'brand' => $brand
        )
      ));
    }
    
    // Chart 9
    // - Intro
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/chart09intro.html.twig'
    ));
    // - Charts
    $brandCount = count($formData['brands']);
    foreach ($formData['markets'] as $marketIdx => $market) {
      foreach ($formData['brands'] as $brandIdx => $brand) {
        // Get subdata
        $filters = array(
          'countries' => $market,
          'brand' => $brand
        );
        $subdata = $this->chart_helper->getDataStructure($fqg->getId(), $filters);
        $datatable = json_encode($this->getChartDataStructuresByMachineName($subdata, 'DNA')['datatable']);
        $map->add(array(
          'twig' => 'PSLClipperBundle:Charts:nps_plus/chart09.html.twig',
          'placeholders' => array(
            'chart_datatable' => json_encode($datatable),
            'subsection_number' => ($marketIdx * $brandCount) + ($brandIdx + 1),
            'brand' => $brand,
            'country' => $market
          )
        ));
      }
    }

    // Appendix
    $map->add(array(
      'twig' => 'PSLClipperBundle:Charts:nps_plus/appendix.html.twig'
    ));
    
    return $map;
  }
  
  /**
   * @param \PSL\ClipperBundle\Entity\FirstQGroup $fqg
   * 
   * @return ArrayCollection $dataStrcutures
   **/
  protected function getDataStructures(FirstQGroup $fqg)
  {
    $dataStructures = new ArrayCollection();
    $drilldowns = new ArrayCollection();
    /**
     * $drilldowns = array(
     *    array(
     *      'country'   => '',
     *      'region'    => '',
     *      'specialty' => '',
     *      'brand'     => '',
     *    )
     * );
     **/

    // drilldowns - region
    $elems = $this->geoMapper->findRegionsByMarkets($fqg->getFormDataByField('markets'));

    foreach ($elems as $elem) {
      $drilldowns->add(
        array(
          'filter_type' => 'region',
          'filters' => array(
            'region' => $elem 
          )
        )
      );
    }
    // drilldowns - country/brand
    $cs = $fqg->getFormDataByField('markets');
    $bs = $fqg->getFormDataByField('brands');
    foreach ($cs as $c) {
      foreach ($bs as $b) {
        $drilldowns->add(
          array(
            'filter_type' => 'country/brand',
            'filters' => array(
              'country' => $c,
              'brand' => $b, 
            )
          )
        );
      }
    }
    // drilldowns - brand
    $elems = $fqg->getFormDataByField('brands');
    foreach ($regions as $elem) {
      $drilldowns->add(
        array(
          'filter_type' => 'brand',
          'filters' => array(
            'brand' => $elem 
          )
        )
      );
    }
    
    // ...add more drilldowns here
    
    // dataStructures
    foreach ($drilldowns as $filters) {
      $tmp = array(
        'filter_type' => $filters['filter_type'],
        'structure' => $this->chart_helper->getDataStructure($fqg->getId(), $filters['filters'])
      );
      $dataStructures->add($tmp);
    }
    
    return $dataStructures;
  }

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