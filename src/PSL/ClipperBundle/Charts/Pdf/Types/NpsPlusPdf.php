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
  protected function onPdf(ChartEvent $event, $eventName, EventDispatcherInterface $dispatcher)
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
    // get data structures
    $dataStructures = $this->getDataStructures($this->fqg);
    
    // get pdfs
    $pdfs = $this->getPdfs($dataStructures);
    
    // set pages in event object
    $event->setPdfFiles($pdfs);
  }
  
  /**
   * @param ArrayCollection $dataTables
   * 
   * @return ArrayCollection $pdfs
   **/
  protected function getPdfs(ArrayCollection $dataStrcutures)
  {
    // create collection of pages
    $pdfs = new ArrayCollection();
    
    foreach ($dataStrcutures as $key => $dataStrcuture) {
      $pages = new ArrayCollection();
      
      /**
       * @todo: get list of twig files based on values from $dataStructure
       **/
      $tpls = array(
        'PSLClipperBundle:charts:nps_plus/chart01.html.twig' => array(),
        'PSLClipperBundle:charts:nps_plus/chart01.html.twig' => array(),
      );
      foreach ($tpls as $tpl => $plh) {
        $html = $this->templating->render($tpl, $plh);
        $pages->add($html);
      }
      // render pages into PDF files
      // $pdf = $this->container->get('knp_snappy.pdf')->getOutputFromHtml($pages); // headless browser
      $hash = uniqid();
      $filepath = $this->container->get('kernel')->getRootDir() . '/../web/bundles/pslclipper/pdf/' . $hash . '.pdf';
      $this->container->get('knp_snappy.pdf')->generateFromHtml($pages, $filepath); // headless browser
      $pdfs->add($filepath);
    }
    
    return $pdfs;
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
}