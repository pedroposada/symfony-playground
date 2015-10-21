<?php
/**
 * PHPUnit Test
 * Download Controller, Event & Output Component Tests
 *
 * Download Controller
 * src/PSL/ClipperBundle/Controller/DownloadsController.php
 *
 * Direct test command:
 * phpunit -c app src/PSL/ClipperBundle/Tests/Controller/ChartsControllerTest.php
 */


// src/PSL/ClipperBundle/Tests/Controller/ChartsControllerTest.php

namespace PSL\ClipperBundle\Tests\Controller;

use Symfony\Component\DomCrawler\Crawler;

use PSL\ClipperBundle\Tests\WebTestCase;

/**
 * ChartsControllerTest test.
 */
class ChartsControllerTest extends WebTestCase
{
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
   * @depends testGetOrderId
   */
  // public function testChartsAction($order_id)
  // {
  //   $this->markTestSkipped('This method / endpoint was marked depreciated.');
  // }

  /**
   * @depends testGetOrderId
   */
  public function testChartsReactAction($order_id)
  {
    $uri = $this->getUrl('psl_clipper_charts_react');

    $client = static::createClient();
    $client->request('GET', $uri, array(
      'order_id' => $order_id
    ));
    $response = $client->getResponse();

    //Response code: 200
    $this->assertTrue($response->isSuccessful());

    //Content type
    $this->assertSame('text/html; charset=UTF-8', $response->headers->get('Content-Type'));

    $content = $response->getContent();
    $content = new Crawler($content);
    $content = $content->filterXPath("//html/body/div[@id=\"react-content\"][@data-order_id=\"{$order_id}\"]")->count();
    $this->assertNotEmpty($content);
  }

  /**
   * Test React POST Endpoint without any Drilldown.
   * @method  testPostReactAction
   *
   * @depends testGetOrderId
   */
  public function testPostReactAction($order_id)
  {
    $uri = $this->getUrl('psl_clipper_charts_react') . 's';

    $client = static::createClient();
    $client->request('POST', $uri, array(
      'order_id' => $order_id
    ));
    $response = $client->getResponse();

    $this->checkBasicStructure($response, FALSE);
  }

  /**
   * Test React POST Endpoint with a specific chart Drilldown.
   * @method  testPostReactAction
   *
   * @depends testGetOrderId
   */
  public function testPostReactActionWithDrilldownSpecific($order_id)
  {
    $uri = $this->getUrl('psl_clipper_charts_react') . 's';

    $client = static::createClient();
    $client->request('POST', $uri, array(
      'order_id'         => $order_id,
      'specialty'        => 'Cardiology',
      'chartmachinename' => 'NPS',
    ));
    $response = $client->getResponse();

    $this->checkBasicStructure($response, 'NPS');
  }

  private function checkBasicStructure($response, $filtered = FALSE)
  {
    //Response code: 200
    $this->assertTrue($response->isSuccessful());

    //Content type
    $this->assertSame('application/json', $response->headers->get('Content-Type'));

    $content = $response->getContent();

    $content = json_decode($content, TRUE);
    $this->assertInternalType('array', $content);

    $content = $content['content'];

    //charts
    $this->assertArrayHasKey('charts', $content);
    $set = array(
      'chartmachinename',
      'drilldown',
      'filter',
      'countTotal',
      'countFiltered',
      'datatable',
      'titleLong',
      'charttype',
      'header',
      'footer',
    );
    foreach ($content['charts'] as $index => $chart) {
      foreach ($set as $st) {
        $this->assertArrayHasKey($st, $chart);

        if (($filtered === TRUE) && in_array($st, array('filter'))) {
          // drilldown all @depreciated
          $this->assertNotEmpty($chart[$st], "Chart indexed {$index} empty key '{$st}'.");
        }
        elseif ((!empty($filtered)) && ($filtered == $chart['chartmachinename']) && in_array($st, array('filter'))) {
          // drilldown specific
          $this->assertNotEmpty($chart[$st], "Chart indexed {$index} empty key '{$st}'.");
        }
        elseif (!in_array($st, array('filter'))) {
          // no drilldown
          $this->assertNotEmpty($chart[$st], "Chart indexed {$index} empty key '{$st}'.");
        }
      }
    }

    // meta
    $this->assertArrayHasKey('meta', $content);
    $set = array(
      'projectTitle',
      'totalResponses',
      'quota',
      'finalReportReady',
      'introduction',
      'introImage',
      'conclusion',
      'reportDescriptionTitle',
      'reportDescription',
      'appendix',
    );
    foreach ($set as $st) {
      $this->assertArrayHasKey($st, $content['meta']);
      $this->assertNotEmpty($content['meta'][$st]);
    }
  }
}
