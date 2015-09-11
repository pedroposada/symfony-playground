<?php

namespace PSL\ClipperBundle\Charts;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;

use PSL\ClipperBundle\ClipperEvents;
use PSL\ClipperBundle\Event\ChartEvent;

class Assembler
{
  protected $container;
  protected $logger;
  protected $em; // entity manager
  protected $params;
  protected $responses;
  protected $dispatcher;

  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
    $this->em = $this->container->get('doctrine')->getManager();
    $this->logger = $this->container->get('monolog.logger.clipper');
    $this->params = $this->container->getParameter('clipper');
    $this->dispatcher = $this->container->get('event_dispatcher');
  }

  /**
   * Set ChartEvent
   * 
   * @param $order_id UUID of the FirstQGroup
   * @param $chart_type string, unique identifier for the chart type
   * @param $survey_type string, unique identifier for the survey type
   * @param $drilldown array of additional filters
   *
   * @return $event \PSL\ClipperBundle\Event\ChartEvent
   */
  private function setChartEvent($order_id, $chart_type, $survey_type, $drilldown = array())
  {
    $event = new ChartEvent();
    $event->setOrderId($order_id);
    $event->setParams($drilldown);
    $event->setChartType($chart_type);
    $fqg = $this->em->getReference('PSLClipperBundle:FirstQGroup', $order_id);
    $responses = $this->em->getRepository('PSLClipperBundle:LimeSurveyResponse')->findByFirstqgroup($fqg);
    $responses = new ArrayCollection($responses);
    
    if ($first = $responses->first()) {
      $event->setBrands($first->getFirstqgroup()->getFormDataByField('brands'));
      $event->setParams($first->getFirstqgroup()->getFormDataByField('attributes'));
      $event->setData($responses);
      $event->setSurveyType($survey_type);
      $this->dispatcher->dispatch(ClipperEvents::CHART_PROCESS, $event);
    }
    
    return $event;
  }
    
  /**
   * Get data table to render a chart
   * 
   * @see setChartEvent()
   */
  public function getChartEvent($order_id, $chart_type, $survey_type, $drilldown = array())
  {
    return $this->setChartEvent($order_id, $chart_type, $survey_type, $drilldown);
  }

  /**
   * Get data table to render a chart
   * 
   * @see setChartEvent()
   */
  public function getChartDataTable($order_id, $chart_type, $survey_type, $drilldown = array())
  {
    return $this->setChartEvent($order_id, $chart_type, $survey_type, $drilldown)->getDataTable();
  }

}
