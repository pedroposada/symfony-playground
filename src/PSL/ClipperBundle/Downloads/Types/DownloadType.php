<?php
namespace PSL\ClipperBundle\Downloads\Types;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use PSL\ClipperBundle\Event\DownloadEvent;

abstract class DownloadType
{
  //Assembler defined variables
  protected $container;
  protected $logger;

  //Event variables
  protected $survey_machine_name;

  public function __construct(ContainerInterface $container, $survey_machine_name) {
    $this->container           = $container;
    $this->logger              = $container->get('monolog.logger.clipper');
    $this->survey_machine_name = $survey_machine_name;
  }

  /**
   * @method onDownload
   *
   * @param  DownloadEvent $event
   * @param  string $eventName @uses ClipperEvents::DOWNLOAD_PROCESS
   * @param  EventDispatcherInterface $dispatcher
   *
   * @return void
   */
  public function onDownload(DownloadEvent $event, $eventName, EventDispatcherInterface $dispatcher) {    
    if ($event->getDispatcherEventName() == $this->survey_machine_name) {
      $this->logger->debug("eventName: {$eventName}");
      
      $event->setFile($this->exportFile($event));      
    }
  }

  /**
   * @param DownloadEvent $event ChartEvent
   *
   * @return Symfony\Component\BrowserKit\Response object of an export file
   */
  abstract protected function exportFile(DownloadEvent $event);
}