<?php

namespace PSL\ClipperBundle\Downloads;

use PSL\ClipperBundle\ClipperEvents;
use PSL\ClipperBundle\Event\DownloadEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Assembler
{
  protected $container;
  protected $logger;
  protected $dispatcher;

  public function __construct(ContainerInterface $container)
  {
    $this->container  = $container;
    $this->logger     = $this->container->get('monolog.logger.clipper');
    $this->dispatcher = $this->container->get('event_dispatcher');
  }

  /**
   * Set DownloadEvent
   *
   * @param string  $order_id
   *    UUID of the FirstQGroup.
   *
   * @param string  $survey_type
   *    Unique identifier for the survey type.
   *
   * @param string  $download_type
   *    Download format; @see self::$type_map.
   *
   * @param array   $raw_data string
   *    Associated array data needed for the download processor.
   *
   * @return $event \PSL\ClipperBundle\Event\DownloadEvent
   */
  private function setDownloadEvent($order_id, $survey_type, $download_type, $raw_data)
  {
    $event = new DownloadEvent();

    $event->setOrderId($order_id);
    $event->setSurveyType($survey_type);
    $event->setDownloadType($download_type);
    $event->setRawData($raw_data);
    $this->dispatcher->dispatch(ClipperEvents::DOWNLOAD_PROCESS, $event);

    return $event;
  }

  /**
   * Get data table to render response, and return it.
   *
   * @see setDownloadEvent()
   */
  public function getDownloadFile($order_id, $survey_type, $download_type, $raw_data)
  {
    return $this->setDownloadEvent($order_id, $survey_type, $download_type, $raw_data)->getFile();
  }
}
