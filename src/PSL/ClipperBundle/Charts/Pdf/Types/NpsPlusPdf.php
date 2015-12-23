<?php

/**
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
    // get pdfs
    $pdfs = $this->getPdfs();
    
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
        'PSLClipperBundle:charts:nps_plus/chart01.html.twig',
        'PSLClipperBundle:charts:nps_plus/chart01.html.twig',
      );
      foreach ($tpls as $key => $tpl) {
        $html = $this->templating->render($tpl);
        $pages->add($html);
      }
      // render pages into PDF files
      $pdf = $this->container->get('knp_snappy.pdf')->getOutputFromHtml($pages); // headless browser
      $pdfs->add($pdf);
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

    // drilldowns - regions
    $regions = $this->geoMapper->findRegionsByMarkets($fqg->getFormDataByField('markets'));
    foreach ($regions as $region) {
      $drilldowns->add(
        array(
          'region' => $region 
        )
      );
    }
    
    // ...add more drilldowns here
    
    // dataStructures
    foreach ($drilldowns as $filters) {
      $dataStructures->add($this->chart_helper->getDataStructure($fqg->getId(), $filters));
    }
    
    return $dataStructures;
  }
}