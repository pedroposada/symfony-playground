<?php

// src/PSL/ClipperBundle/Tests/Controller/ClipperControllerTest.php

namespace PSL\ClipperBundle\Tests\Controller;

use PSL\ClipperBundle\Tests\WebTestCase;

/**
 * ClipperControllerTest test.
 */
class ClipperControllerTest extends WebTestCase
{
    /**
     * @dataProvider autocompleteParametersProvider
     */
    public function testGetClipperAutocompleteAction($parameters, $result)
    {
        $uri = $this->getUrl('get_clipper_autocomplete');
        $this->client->request('GET', $uri, $parameters);

        // Assert that the response status code is 2xx.
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // Assert a specific 200 status code.
        $this->assertEquals(
            200,
            $this->client->getResponse()->getStatusCode()
        );

        // Assert that the "Content-Type" header is "application/json".
        $this->assertEquals(
            'application/json',
            $this->client->getResponse()->headers->get('content-type')
        );

        // Decode response content data.
        $content = json_decode($this->client->getResponse()->getContent(), true);

        // Assert that the response content contains content
        $this->assertArrayHasKey('content', $content);

        // Assert that the response content is not empty.
        $this->assertNotEmpty($content);

        // TODO: mock the result in clipper.brands and clipper.conditions.
        // Find a way to set parameters for test environment.
        // $this->assertEquals($result, $content);
    }

    public function autocompleteParametersProvider()
    {
        return array(
            // ?keyword=a
            'Has keyword only' => array(
                array(
                    'keyword' => 'a',
                ),
                array(),
            ),
            // ?group=brands&keyword=a
            'Has brand and keyword' => array(
                array(
                    'group' => 'brands',
                    'keyword' => 'a',
                ),
                array(),
            ),
            // ?group=conditions&keyword=a
            'Has condition and keyword' => array(
                array(
                    'group' => 'conditions',
                    'keyword' => 'a',
                ),
                array(),
            ),
        );
    }

    /**
     * @dataProvider postOrderDataProvider
     */
    public function testPostOrderAction($postData)
    {
        $uri = $this->getUrl('post_neworder');
        $this->client->request('POST', $uri, array(), array(), array('CONTENT_TYPE' => 'application/json'), $postData);

        // Assert that the response status code is 2xx.
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // Assert a specific 200 status code.
        $this->assertEquals(
            200,
            $this->client->getResponse()->getStatusCode()
        );

        // Assert that the "Content-Type" header is "application/json".
        $this->assertEquals(
            'application/json',
            $this->client->getResponse()->headers->get('content-type')
        );

        // Assert that the response content contains a string.
        $this->assertContains(
            'firstq_uuid',
            $this->client->getResponse()->getContent()
        );
    }

    public function postOrderDataProvider()
    {
        return array(
            array('Test Set 1' => json_encode(array(
                'ir' => 20,
                'name' => 'a name',
                'title' => 'a title',
                'name_full' => 'a name full',
                'patient_type' => 'sick',
                'timestamp' => 1436452135,
                'market' => array(
                    'USA',
                ),
                'specialty' => array(
                    'Oncology',
                    'Cardiology',
                ),
                'survey_brand' => array(
                    'AA-123',
                    'BB-456',
                    'CC-789',
                    'DD-123',
                    'EE-456',
                    'FF-789',
                ),
                'attribute' => array(
                    'it just works',
                    'painfull side effects',
                    'risk of death',
                    'just painful',
                    'mildly pointless',
                    'kind of cool',
                    'not effective',
                    'gives headaches',
                    '',
                ),
                'launch_date' => '2015-07-22 11:10:33',
                'timezone_client' => 'Europe/London',
            ))),
        );
    }

    public function testGetOrdersAction()
    {
        $uri = $this->getUrl('get_orders', array('user_id' => 123));
        $this->assertBehindFirewall('GET', $uri);

        $this->authenticatedClient->request('GET', $uri);
        $content = $this->authenticatedClient->getResponse()->getContent();
        $content = json_decode($content, true);

        $this->assertEquals(
            array(
                'content' => 'No orders.',
                'status' => 204,
                'headers' => array(),
            ),
            $content
        );
    }

    public function testGetOrderAction()
    {

    }

    public function testPostOrderProcessAction()
    {

    }

    public function testDebugAction()
    {

    }

    public function testExitAction()
    {

    }

    public function testThankyouAction()
    {

    }

    public function testRedirectLimeSurveyAction()
    {

    }

    public function testAutocompleteAction()
    {

    }
}
