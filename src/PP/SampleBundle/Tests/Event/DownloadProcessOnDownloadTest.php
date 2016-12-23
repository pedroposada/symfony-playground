<?php
/**
 * PHPUnit Test
 * Download Component Tests
 *
 * Download Assembler tests
 * src/PP/SampleBundle/Tests/Event/DownloadProcessOnDownloadTest.php
 *
 * Direct test command:
 * phpunit -c app src/PP/SampleBundle/Tests/Event/DownloadProcessOnDownloadTest.php
 */
namespace PP\SampleBundle\Event;

use PP\SampleBundle\ClipperEvents;
use PP\SampleBundle\Event\DownloadEvent;
use PP\SampleBundle\Tests\WebTestCase;

class DownloadProcessOnDownloadTest extends WebTestCase
{
  /**
   * Test Download Event Type & Download to not throw errors.
   * @method  testDownloadEventThrow
   *
   * @dataProvider dataDownloadEventThrow
   */
  public function testDownloadEventThrow($data)
  {
    $default = array(
      'survey_type'   => '',
      'download_type' => '',
    );
    $data = array_merge($default, $data);

    $event = new DownloadEvent();
    $error = '';
    try {
      $event->setSurveyType($data['survey_type']);
      $event->setDownloadType($data['download_type']);
    }
    catch (\Exception $e) {
      $error = $e->getMessage();
      $this->fail("Download event render fail and throw: '{$error}' with args: '{$data}'.");
      return;
    }
    $this->assertEmpty($error);
  }

  /**
   * Provider for testDownloadEventThrow
   * @method dataDownloadEventThrow
   *
   * @return array
   */
  public function dataDownloadEventThrow()
  {
    return array(
      'Event with support arg 001.' => array(  // TODO: remove
        array(
          'survey_type'   => 'nps_plus',
          'download_type' => 'dev',
        )
      ),
      'Event with support arg 002.' => array(
        array(
          'survey_type'   => 'nps_plus',
          'download_type' => 'xls',
        )
      ),
    );
  }

  /**
   * Test Download Event Type & Download to throw errors.
   * @method  testDownloadEventThrowError
   *
   * @dataProvider dataDownloadEventThrowError
   */
  public function testDownloadEventThrowError($data)
  {
    $default = array(
      'survey_type'   => '',
      'download_type' => '',
    );
    $data = array_merge($default, $data);

    $event = new DownloadEvent();
    $error = '';
    try {
      $event->setSurveyType($data['survey_type']);
      $event->setDownloadType($data['download_type']);
    }
    catch (\Exception $e) {
      $error = $e->getMessage();
      $this->assertContains('Unsupported ', $error);
      return;
    }

    $data = json_encode($data);
    $this->fail("Download event fails to throw error for: '{$data}'.");
  }

  /**
   * Provider for testDownloadEventThrowError
   * @method dataDownloadEventThrowError
   *
   * @return array
   */
  public function dataDownloadEventThrowError()
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
          'survey_type'   => 'nps_plus',
          'download_type' => 'doc',
        )
      ),
    );
  }
}