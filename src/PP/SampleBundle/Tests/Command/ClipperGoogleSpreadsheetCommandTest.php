<?php
/**
 * PHPUnit Test
 * Google Spreadsheet & Cache Component Tests
 *
 * Google Spreadsheet Command tests
 * src/PP/SampleBundle/Command/ClipperGoogleSpreadsheetCommand.php
 *
 * Direct test command:
 * phpunit -c app src/PP/SampleBundle/Tests/Command/ClipperGoogleSpreadsheetCommandTest.php
 */
namespace PP\SampleBundle\Tests\Command;

use PP\SampleBundle\Tests\WebTestCase;

class ClipperGoogleSpreadsheetCommandTest extends WebTestCase
{
  /**
   * @method testExecute
   */
  public function testExecute()
  {
    $output = $this->runCustomCommand("clipper:gdoc-auth-refresh -f -v");
    $this->assertContains('clipper.INFO:', $output);
    $this->assertContains('Success: Access token is active.', $output, 'Check Clipper Cache service might on Inactive state.');

    //flush all cache
    $output = $this->runCustomCommand("clipper:cache flush all -v");
    $this->assertContains('Flush complete', $output);
  }
}
