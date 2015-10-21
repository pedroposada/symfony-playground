<?php
/**
 * NPSPlusDev
 *
 *  FOR DEVELOPMENT TEMPLATE
 *  @TODO remove reference on service and assembler level.
 *
 * Survey Type  = NPS Plus
 * Machine Name = nps_plus
 * Export Into  = nothing - return all back in array
 * Service Name = clipper.download.nps_plus_dev
 */
namespace PSL\ClipperBundle\Downloads\Types;

use PSL\ClipperBundle\Downloads\Types\DownloadType;
use PSL\ClipperBundle\Event\DownloadEvent;

class NPSPlusDev extends DownloadType
{
  protected $data;

  /**
   * Main method to process data into a file.
   * @method exportFile
   *
   * @param  PSL\ClipperBundle\Event\DownloadEvent $event
   *
   * @return Symfony\Component\BrowserKit\Response object of an export file
   */
  public function exportFile(DownloadEvent $event) {
    //get data
    $order_id    = $event->getOrderId();
    $survey_type = $event->getSurveyType();
    $this->data  = $event->getRawData();

    return array(
      'order-id'     => $order_id,
      'survey_type'  => $survey_type,
      'data'         => $this->data,
    );
  }
}
