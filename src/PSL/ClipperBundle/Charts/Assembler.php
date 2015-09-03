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
   * Get data table to render a chart
   *
   * @param $order_id UUID of the FirstQGroup
   * @param $chart_type string, unique identifier for the chart type
   * @param $survey_type string, unique identifier for the survey type
   * @param $params array of additional filters
   *
   * @return $this
   */
  public function getChartDataTable($order_id, $chart_type, $survey_type, $params = array())
  {
    $event = new ChartEvent();
    $event->setOrderId($order_id);
    $event->setParams($params);
    $event->setChartType($chart_type);
    $fqg = $this->em->getReference('PSLClipperBundle:FirstQGroup', $order_id);
    $responses = $this->em->getRepository('PSLClipperBundle:LimeSurveyResponse')->findByFirstqgroup($fqg);
    $responses = new ArrayCollection($responses);
    $event->setBrands($responses->first()->getFirstqgroup()->getFormDataByField('brands'));
    $event->setParams($responses->first()->getFirstqgroup()->getFormDataByField('attributes'));
    $event->setData($responses);
    $event->setSurveyType($survey_type);
    $this->dispatcher->dispatch(ClipperEvents::CHART_PROCESS, $event);

    return $event->getDataTable();
  }

}
