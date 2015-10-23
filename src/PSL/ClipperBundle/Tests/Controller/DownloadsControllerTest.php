<?php
/**
 * PHPUnit Test
 * Download Controller, Event & Output Component Tests
 *
 * Download Controller
 * src/PSL/ClipperBundle/Controller/DownloadsController.php
 *
 * Direct test command:
 * phpunit -c app src/PSL/ClipperBundle/Tests/Controller/DownloadsControllerTest.php
 */
namespace PSL\ClipperBundle\Tests\Controller;

use PSL\ClipperBundle\Tests\WebTestCase;
use Symfony\Component\Routing\RequestContext;

class DownloadsControllerTest extends WebTestCase
{
  /**
   * Method to return latest completed NPS Plus Order ID.
   * @method testGetOrderId
   *
   * @return string
   */
  public function testGetOrderId()
  {
    $order_id = $this->getLatestFirstQGroup('nps_plus', 'ORDER_COMPLETE', 'Id');

    //not empty
    $this->assertNotEmpty($order_id);

    //length
    $this->assertSame(36, strlen($order_id));

    return $order_id;
  }

  /**
   * Method to test Download controller for NPS Plus with 'dev' format.
   * @method testDownloadsActionNPSPlusDev
   *
   * @TODO: remove, for development
   *
   * @depends testGetOrderId
   */
  public function testDownloadsActionNPSPlusDev($order_id)
  {
    $client = static::createClient();
    $client->insulate();

    $download_uri = $this->getUrl('downloads');
    $client->request('GET', $download_uri, array(
      'order_id' => $order_id,
      'type'     => 'dev',
    ));
    $response = $client->getResponse();
    $client->restart();

    //Response code: 200
    $this->assertTrue($response->isSuccessful());

    //Content type
    $this->assertSame('application/json', $response->headers->get('Content-Type'));

    //Content extraction
    $content = $response->getContent();
    $content = json_decode($content, TRUE);
    $this->assertInternalType('array', $content);

    //Content validation
    $this->assertArrayHasKey('survey_type', $content);
    $this->assertArrayHasKey('complete', $content['data']);
    $this->assertArrayHasKey('available-drilldown', $content['data']);
    $this->assertArrayHasKey('available-brands', $content['data'], 'Test survey group not have any respondent.');
    $this->assertArrayHasKey('available-charts', $content['data']);
    $this->assertArrayHasKey('charts-table-map', $content['data']);
    $this->assertArrayHasKey('filtered', $content['data']);

    //Content specific validation
    $this->assertSame($order_id, $content['order-id']);
    $this->assertSame('nps_plus', $content['survey_type']);
  }

  /**
   * Method to test Download controller for NPS Plus with 'xls' type.
   * @method testDownloadsActionNPSPlusExcel
   *
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
    $this->client->restart();

    //clean up
    ob_end_clean();

    //Response code: 200
    $this->assertTrue($response->isSuccessful());

    //Content type
    $this->assertSame('application/vnd.ms-excel; charset=utf-8', $response->headers->get('Content-Type'));
  }

  /**
   * Method to test Download controller for NPS Plus with 'make' type.
   * @method testDownloadsActionNPSPlusError
   *
   * @depends testGetOrderId
   */
  public function testDownloadsActionNPSPlusError($order_id)
  {
    $client = static::createClient();
    $client->insulate();

    $download_uri = $this->getUrl('downloads');
    $client->request('GET', $download_uri, array(
      'order_id' => $order_id,
      'type'     => 'csv',
    ));
    $response = $client->getResponse();
    $client->restart();

    //Response code: 200
    $this->assertFalse($response->isSuccessful());
  }
}