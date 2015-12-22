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

class NpsPlusPdf
{
  protected $container;
  protected $em;
  protected $logger;
  protected $survey_type;  
  
  /**
   * @param ContainerInterface $container
   * @param string $survey_type
   */
  public function __construct(ContainerInterface $container, $survey_type) 
  {
    $this->container        = $container;
    $this->em               = $container->get('doctrine')->getManager();
    $this->logger           = $container->get('monolog.logger.clipper');
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
    $fqg = $this->em->getReference('PSLClipperBundle:FirstQGroup', $event->getOrderId());
    
    // check for type
    if (current($fqg->getFormDataByField('survey_type')) === $this->survey_type) {
      $this->main($event);
    }
  }
  
  /**
   * @param ChartEvent $event ChartEvent
   */
  protected function main(ChartEvent $event)
  {
    // create collection of pages
    $pdfs = new ArrayCollection();
    // call postReactAction
    $html = $this->container->renderView('ClipperBundle:Charts:nps_plus/chart01.html.twig', array(
      'clippercharts' => '', 
      'chartid' => '',
    ));
    $pdf = $this->container->get('knp_snappy.pdf')->getOutputFromHtml($html); // headless browser
    $pdfs->add($pdf);
    
    // render pages into PDF files
    
    
    // set pages in event object
    $event->setPdfFiles($pdfs);
  }
}