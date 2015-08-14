<?php

// src/PSL/ClipperBundle/Tests/Controller/ClipperControllerTest.php

namespace PSL\ClipperBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ClipperControllerTest extends WebTestCase
{
    /**
     * @dataProvider autocompleteParametersProvider
     */
    public function testGetClipperAutocompleteAction($parameters)
    {
        $client = static::createClient();
        $uri = $client->getContainer()->get('router')->generate('get_clipper_autocomplete');
        $client->request('GET', $uri, $parameters);

        // Assert that the response status code is 2xx
        $this->assertTrue($client->getResponse()->isSuccessful());

        // Assert a specific 200 status code
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );

        // Assert that the "Content-Type" header is "application/json"
        $this->assertEquals(
            'application/json',
            $client->getResponse()->headers->get('content-type')
        );

        // Assert that the response content contains a string
        $this->assertContains(
            '"content":[',
            $client->getResponse()->getContent()
        );

        // Assert that the response content is not empty
        $content = json_decode($client->getResponse()->getContent());
        $this->assertNotEmpty($content->content);
    }

    public function autocompleteParametersProvider()
    {
        return array(
            // ?keyword=a
            'Has keyword only' => array(array(
                'keyword' => 'a',
            )),
            // ?group=brands&keyword=a
            'Has brand and keyword' => array(array(
                'group' => 'brands',
                'keyword' => 'a',
            )),
            // ?group=conditions&keyword=a
            'Has condition and keyword' => array(array(
                'group' => 'conditions',
                'keyword' => 'a',
            )),
        );
    }

    /**
     * @dataProvider postOrderDataProvider
     */
    public function testPostOrderAction($postData)
    {
        $client = static::createClient();
        $uri = $client->getContainer()->get('router')->generate('post_order');
        $client->request('POST', $uri, array(), array(), array('CONTENT_TYPE' => 'application/json'), $postData);

        // Assert that the response status code is 2xx
        $this->assertTrue($client->getResponse()->isSuccessful());

        // Assert a specific 200 status code
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );

        // Assert that the "Content-Type" header is "application/json"
        $this->assertEquals(
            'application/json',
            $client->getResponse()->headers->get('content-type')
        );

        // Assert that the response content contains a string
        $this->assertContains(
            'firstq_uuid',
            $client->getResponse()->getContent()
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
}
