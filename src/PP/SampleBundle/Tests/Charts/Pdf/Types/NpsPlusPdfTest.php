<?php
/**
 * PHPUnit Test
 * Pdf generation for reports (charts and more).
 *
 * Direct test command:
 * phpunit -c app src/PP/SampleBundle/Tests/Charts/Pdf/Types/NpsPlusPdfTest.php
 *
 * @see  src/PP/SampleBundle/Charts/Pdf/Types/NpsPlusPdf.php
 */
namespace PP\SampleBundle\Tests\Charts\Pdf\Types;

use PP\SampleBundle\Tests\WebTestCase;
use PP\SampleBundle\Charts\Pdf\Types\NpsPlusPdf;

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
    $method = $this->getPrivateMethod( 'PP\SampleBundle\Charts\Pdf\Types\NpsPlusPdf', 'getPdfs' );
    
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