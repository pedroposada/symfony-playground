<?php
/**
 * PHPUnit Test
 * Clipper Cache Component Tests
 *
 * Clipper Cache Command tests
 * src/PSL/ClipperBundle/Command/ClipperCacheCommand.php
 *
 * Direct test command:
 * phpunit -c app src/PSL/ClipperBundle/Tests/Command/ClipperCacheCommandTest.php
 */
namespace PSL\ClipperBundle\Tests\Command;

use PSL\ClipperBundle\Tests\WebTestCase;

class ClipperCacheCommandTest extends WebTestCase
{
  /**
   * @method  testExecute
   *
   * @dataProvider dataExecute
   */
  public function testExecute($command, $status, $contains, $message = NULL)
  {
    $contains = (array) $contains;

    $output = $this->runCustomCommand($command);
    $this->assertContains('clipper.' . $status . ':', $output);
    foreach ($contains as $contain) {
      $this->assertContains($contain, $output, $message);
    }
  }

  /**
   * Data provider for testExecute()
   * @method dataExecute
   *
   * @return array
   */
  public function dataExecute()
  {
    return array(
      'Cache Action Status' => array(
        'clipper:cache status -v',
        'INFO',
        'ClipperCache is active',
        'ClipperCache is Inactive'
      ),
      'Cache Action Get' => array(
        'clipper:cache get this-cache-is-not-available -v',
        'ERROR',
        'Requested Cache named \'this-cache-is-not-available\' was not available.'
      ),
      'Cache Action Flush Expired' => array(
        'clipper:cache flush -v',
        'INFO',
        array('Flushing expired cache records', 'Flush complete'),
      ),
      'Cache Action Flush All' => array(
        'clipper:cache flush all -v',
        'INFO',
        array('Flushing all cache records', 'Flush complete'),
      ),
    );
  }
}
