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
  protected $survey_type;
  protected $download_type;

  public function __construct(ContainerInterface $container, $survey_type, $download_type) {
    $this->container     = $container;
    $this->logger        = $container->get('monolog.logger.clipper');
    $this->survey_type   = $survey_type;
    $this->download_type = $download_type;
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
    if (($event->getSurveyType() == $this->survey_type) && ($event->getDownloadType() == $this->download_type)) {
      $this->logger->debug("eventName: {$eventName}");
            
      $event->setFile($this->exportFile($event));      
    }
  }
  
  /**
   * Helper method to sanitize filename.
   * @method sanitizeFileName
   *
   * @param  string $filename
   *
   * @return string
   */
  protected function sanitizeFileName($filename)
  {
    return preg_replace("/[^a-zA-Z0-9-_\.]/", "_", $filename);
  }

  /**
   * @param DownloadEvent $event ChartEvent
   *
   * @return Symfony\Component\BrowserKit\Response object of an export file
   */
  abstract protected function exportFile(DownloadEvent $event);
}