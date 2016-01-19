<?php
/**
 * PHPUnit Test
 * Pdf generation for reports (charts and more).
 *
 * Direct test command:
 * phpunit -c app src/PSL/ClipperBundle/Tests/Charts/Pdf/Types/NpsPlusPdfTest.php
 *
 * @see  src/PSL/ClipperBundle/Charts/Pdf/Types/NpsPlusPdf.php
 */
namespace PSL\ClipperBundle\Tests\Charts\Pdf\Types;

use PSL\ClipperBundle\Tests\WebTestCase;
use PSL\ClipperBundle\Charts\Pdf\Types\NpsPlusPdf;

class NpsPlusPdfTest extends WebTestCase
{
  private static $survey_type = 'nps_plus';
  
  /**
   * Test 1: run getPdfs()
   * 
   * @method getPdfs
   **/
  public function testGetPdfs()
  {
    // get protected method
    $object = new NpsPlusPdf($this->container, 'nps_plus');
    $method = $this->getPrivateMethod( 'PSL\ClipperBundle\Charts\Pdf\Types\NpsPlusPdf', 'getPdfs' );
    
    // this function 
    $result = $method->invokeArgs($object, array()); // no args
    
    /**
     * assertions
     **/
    $this->assertInstanceOf(
      '\Doctrine\Common\Collections\ArrayCollection', 
      $result, 
      '$result is not an \Doctrine\Common\Collections\ArrayCollection'
    );
    
    
  }
}