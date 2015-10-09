<?php
// phpunit -c app src/PSL/ClipperBundle/Tests/Controller/DownloadsControllerTest.php

namespace PSL\ClipperBundle\Tests\Controller;

use PSL\ClipperBundle\Tests\WebTestCase;
use Symfony\Component\Routing\RequestContext;

class DownloadsControllerTest extends WebTestCase
{  
  public function testGetOrderId()
  {
    $order_id = $this->getLatestFirstQGroupOrderId();
    
    //not empty
    $this->assertNotEmpty($order_id);

    //length
    $this->assertSame(36, strlen($order_id));

    return $order_id;
  }
  
  /**
   * @depends testGetOrderId
   */
  // @TODO: review multiple request tests
  // @ISSUE: $this->client->request response gave same result for next test.
  // - insulate(), cloning, separate test file
  // - OK if execute test separately
  // 
  // public function testDownloadsActionNPSPlusDev($order_id)
  // {
  //   $download_uri = $this->getUrl('downloads');    
  //   $this->client->request('GET', $download_uri, array(
  //     'order_id' => $order_id,
  //     'type'     => 'dev',
  //   ));
  //   $response = $this->client->getResponse();
    
  //   //Response code: 200
  //   $this->assertTrue($response->isSuccessful());

  //   //Content type
  //   $this->assertSame('application/json', $response->headers->get('Content-Type'));

  //   //Content extraction
  //   $content = $response->getContent();
  //   $content = json_decode($content, TRUE);
  //   $this->assertInternalType('array', $content);

  //   //Content validation
  //   $this->assertArrayHasKey('survey_type', $content);
  //   $this->assertArrayHasKey('machine_name', $content);
  //   $this->assertArrayHasKey('complete', $content['data']);
  //   $this->assertArrayHasKey('available-drilldown', $content['data']);
  //   $this->assertArrayHasKey('available-brands', $content['data']);
  //   $this->assertArrayHasKey('available-charts', $content['data']);
  //   $this->assertArrayHasKey('available-charts', $content['data']);
  //   $this->assertArrayHasKey('charts-table-map', $content['data']);
  //   $this->assertArrayHasKey('filtered', $content['data']);

  //   //Content specific validation
  //   $this->assertSame($order_id, $content['order-id']);
  //   $this->assertSame('nps_plus', $content['survey_type']);
  //   $this->assertSame('NPSPlusDev', $content['machine_name']);
  // }
  
  /**
   * @depends testGetOrderId
   */
  public function testDownloadsActionNPSPlusExcel($order_id) 
  {
    //hold the file stream
    ob_start();
    
    $download_uri = $this->getUrl('downloads');
    $this->client->request('GET', $download_uri, array(
      'order_id' => $order_id,
      'type'     => 'xls',
    ));
    $response = $this->client->getResponse();
    
    //clean up
    ob_end_clean();

    //Response code: 200
    $this->assertTrue($response->isSuccessful());
    
    //Content type
    $this->assertSame('application/vnd.ms-excel; charset=utf-8', $response->headers->get('Content-Type'));
  }
}