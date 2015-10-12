<?php
/**
 * PHPUnit Test
 * Download Component Tests
 *
 * Download Assembler tests
 * src/PSL/ClipperBundle/Tests/Event/DownloadProcessOnDownloadTest.php
 *
 * Direct test command:
 * phpunit -c app src/PSL/ClipperBundle/Tests/Event/DownloadProcessOnDownloadTest.php
 */
namespace PSL\ClipperBundle\Event;

use PSL\ClipperBundle\ClipperEvents;
use PSL\ClipperBundle\Event\DownloadEvent;
use PSL\ClipperBundle\Tests\WebTestCase;

class DownloadProcessOnDownloadTest extends WebTestCase
{
  /**
   * Test Download Event Dispatcher Name.
   * @method  testGetDispatcherEventName
   *
   * @dataProvider dataGetDispatcherEventName
   */
  public function testGetDispatcherEventName($data, $result)
  {
    $event = new DownloadEvent();
    $event->setSurveyType($data['survey_type']);
    $event->setDownloadType($data['download_type']);
    $event->setDispatcherEventName();
    $dispatcherEventName = $event->getDispatcherEventName();
    $this->assertNotEmpty($dispatcherEventName);
    $this->assertEquals($result['dispatcher-event-name'], $dispatcherEventName);
  }

  /**
   * Provider for testGetDispatcherEventName
   * @method dataGetDispatcherEventName
   *
   * @TODO: remove dev download_type
   * @return array
   */
  public function dataGetDispatcherEventName()
  {
    return array(
      'Event with NPS Plus for JSON.'        => array(
        array(
          'survey_type'   => 'nps_plus',
          'download_type' => 'dev',
        ),
        array(
          'dispatcher-event-name' => 'NPSPlusDev',
        ),
      ),
      'Event with NPS Plus for Excel.'        => array(
        array(
          'survey_type'   => 'nps_plus',
          'download_type' => 'xls',
        ),
        array(
          'dispatcher-event-name' => 'NPSPlusExcel',
        ),
      ),
    );
  }

  /**
   * Test Download Event Dispatcher Name Triggering Errors.
   * @method  testGetDispatcherEventNameError
   * @dataProvider dataGetDispatcherEventNameError
   * @expectedException Exception
   */
  public function testGetDispatcherEventNameError($data)
  {
    $event = new DownloadEvent();
    if (isset($data['survey_type'])) {
      $event->setSurveyType($data['survey_type']);
    }
    if (isset($data['download_type'])) {
      $event->setDownloadType($data['download_type']);
    }
    $event->setDispatcherEventName();

    $dispatcherEventName = $event->getDispatcherEventName();
    $this->assertEmpty($dispatcherEventName);
  }

  /**
   * Provider for testGetDispatcherEventNameError
   * @method dataGetDispatcherEventNameError
   *
   * @return array
   */
  public function dataGetDispatcherEventNameError()
  {
    return array(
      'Event with no required attributes.'        => array(
        array()
      ),
      'Event with non-supported Survey type.'     => array(
        array(
          'survey_type'   => 'other_survey_type',
          'download_type' => 'xls',
        )
      ),
      'Event with non-supported Download type.'   => array(
        array(
          'survey_type'   => 'other_survey_type',
          'download_type' => 'csv',
        )
      ),
    );
  }
}