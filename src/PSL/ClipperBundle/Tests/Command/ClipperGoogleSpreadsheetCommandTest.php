<?php
/**
 * PHPUnit Test
 * Google Spreadsheet & Cache Component Tests
 *
 * Google Spreadsheet Command tests
 * src/PSL/ClipperBundle/Command/ClipperGoogleSpreadsheetCommand.php
 *
 * Direct test command:
 * phpunit -c app src/PSL/ClipperBundle/Tests/Command/ClipperGoogleSpreadsheetCommandTest.php
 */
namespace PSL\ClipperBundle\Tests\Command;

use PSL\ClipperBundle\Tests\WebTestCase;

class ClipperGoogleSpreadsheetCommandTest extends WebTestCase
{
  /**
   * @method testExecute
   */
  public function testExecute()
  {
    $client = self::createClient();
    $output = $this->runCommand($client, "clipper:gdoc-auth-refresh -f -v");
    $this->assertContains('clipper.INFO:', $output);
    $this->assertContains('Success: Access token is active.', $output, 'Check Clipper Cache service might on Inactive state.');
    
    //flush all cache
    $output = $this->runCommand($client, "clipper:cache flush all -v");
    $this->assertContains('Flush complete', $output);
  }
}