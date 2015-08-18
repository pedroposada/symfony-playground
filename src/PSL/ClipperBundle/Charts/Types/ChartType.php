<?php

namespace PSL\ClipperBundle\Charts\Types;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\SurveyChartMap;

abstract class ChartType
{
  protected $container;
  protected $logger;
  protected $em; // entity manager
  protected $params;
  protected $responses;
  protected $chart_type;
  protected $survey_chart_map;
  protected $explode_tree;
  public $data_table;
  
  public function __construct(ContainerInterface $container, $chart_type)
  {
    $this->container = $container;
    $this->em = $container->get('doctrine')->getManager();
    $this->logger = $container->get('monolog.logger.clipper');
    $this->params = $container->getParameter('clipper');
    $this->chart_type = $chart_type;
    $this->survey_chart_map = $container->get('survey_chart_map');
    $this->explode_tree = $container->get('explode_tree');
  }
  
  public function onDataTable(ChartEvent $event, $eventName, EventDispatcherInterface $dispatcher)
  {
    $this->logger->debug("eventName: {$eventName}");
    
    if ($event->getChartType() === $this->chart_type) {
      $this->logger->debug("eventName: {$eventName}");
      $event->setDataTable($this->dataTable($event));
    }
    
  }
  
  /**
   * @param ChartEvent $event ChartEvent
   * 
   * @return array googlcharts datatable
   */
  abstract protected function dataTable(ChartEvent $event);
  
}
