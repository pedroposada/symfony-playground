<?php
namespace PSL\ClipperBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ChartsControllerTest extends WebTestCase {
  private $em;

  public function setUp() {
    self::bootKernel();
    $this->em = static::$kernel->getContainer()->get('doctrine')->getManager();
  }

  public function testGetOrderId() {
    $groups = $this->em->getRepository('PSLClipperBundle:FirstQGroup')->findAll(array(), NULL, 1);
    foreach ($groups as $group) {
      $order_id = $group->getId();
    }

    //not empty
    $this->assertNotEmpty($order_id);

    //length
    $this->assertSame(36, strlen($order_id));

    return $order_id;
  }

  /**
   * @depends testGetOrderId
   */
  public function testReactEnpoint($order_id) {
    $client = $this->createClient();
    $client->request('POST', '/clipper/charts/reacts', array(
      'order_id' => $order_id
    ));
    $response = $client->getResponse();

    //Response code: 200
    $this->assertTrue($response->isSuccessful());

    //Content-type
    $this->assertSame('application/json', $response->headers->get('Content-Type'));

    //Content
    $content = $response->getContent();
    $content = json_decode($content, TRUE);
    $this->assertInternalType('array', $content);
  }

  /**
   * @depends testGetOrderId
   */
  public function testReactEnpointWithDrillDown($order_id) {
    $client = $this->createClient();
    $client->request('POST', '/clipper/charts/reacts', array(
      'order_id'         => $order_id,
      'specialty'        => 'Cardiology',
      'chartmachinename' => 'NPS',
    ));
    $response = $client->getResponse();

    //Response code: 200
    $this->assertTrue($response->isSuccessful());

    //Content-type
    $this->assertSame('application/json', $response->headers->get('Content-Type'));

    //Content
    $content = $response->getContent();
    $content = json_decode($content, TRUE);
    $this->assertInternalType('array', $content);
  }

  /**
   * @depends testGetOrderId
   */
  public function testDownloadEnpoint($order_id) {
    $this->assertNotEmpty($order_id);
  }

  protected function tearDown() {
      parent::tearDown();
      $this->em->close();
  }
}
