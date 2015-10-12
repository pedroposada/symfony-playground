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

        // Assert the result is correct.
        $this->assertEquals(
            $content['content'],
            $result
        );
    }

    public function autocompleteParametersProvider()
    {
        // Test data located in Tests/Resources/terms/test_{group}.xml.
        return array(
            // ?keyword=a
            'Has keyword only' => array(
                array(
                    'keyword' => 'a',
                ),
                array(
                    'A-Brand-1',
                    'A-Brand-2',
                    'A-Brand-3',
                ),
            ),
            // ?group=brands&keyword=b
            'Has brand and keyword' => array(
                array(
                    'group' => 'brands',
                    'keyword' => 'b',
                ),
                array(
                    'B-Brand-1',
                    'B-Brand-2',
                    'B-Brand-3',
                ),
            ),
            // ?group=conditions&keyword=a
            'Has condition and keyword' => array(
                array(
                    'group' => 'conditions',
                    'keyword' => 'a',
                ),
                array(
                    'A Condition 1',
                    'A Condition 2',
                    'A Condition 3',
                ),
            ),
        );
    }

    /**
     * @dataProvider postOrderDataProvider
     */
    public function testPostNeworderAction($postData)
    {
        $uri = $this->getUrl('post_neworder');
        $client = $this->client;
        $client->insulate(); //avoid using cached Google Sheet service
        $client->request('POST', $uri, array(), array(), array('CONTENT_TYPE' => 'application/json'), $postData);

        // Assert that the response status code is 2xx.
        $this->assertTrue($client->getResponse()->isSuccessful());

        // Assert a specific 200 status code.
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );

        // Assert that the "Content-Type" header is "application/json".
        $this->assertEquals(
            'application/json',
            $this->client->getResponse()->headers->get('content-type')
        );

        // Assert that the response content contains expected format.
        $content = json_decode($client->getResponse()->getContent(), true);
        $content = $content['content'];
        $this->assertTrue(!empty($content['product']));
        $this->assertTrue(!empty($content['product']['price']));
        $this->assertTrue(!empty($content['product']['firstq_uuid']));
        $this->assertTrue(!empty($content['product']['end_date']));

        // Assert the new order is saved.
        $record = $this
            ->getObjectManager()
            ->getRepository('\PSL\ClipperBundle\Entity\FirstQGroup')
            ->findOneBy(array('id' => $content['product']['firstq_uuid']));
        $this->assertEquals(1, count($record));

        // Todo: Assert the new order data is saved correctly.
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
        $uri = $this->getUrl('get_orders', array('user_id' => 'test-user-id-1'));
        $this->assertBehindFirewall('GET', $uri);

        $uri = $this->getUrl('get_orders', array('user_id' => 'wrong-user-id'));
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

        $uri = $this->getUrl('get_orders', array('user_id' => 'test-user-id-1'));
        $this->authenticatedClient->request('GET', $uri);
        $content = $this->authenticatedClient->getResponse()->getContent();
        $content = json_decode($content, true);

        // Assert the returned result is correct.
        $record = $this
            ->getObjectManager()
            ->getRepository('\PSL\ClipperBundle\Entity\FirstQGroup')
            ->findOneBy(array('userId' => 'test-user-id-1'));
        $this->assertEquals(
            $record->getId(),
            $content['content'][0]['id']
        );
    }

    public function testGetOrdersAdminAction()
    {
        $uri = $this->getUrl('get_orders_admin');
        $this->assertBehindFirewall('GET', $uri);

        // Todo: Pending the following exception to be fixed.
        // There is no extension able to load the configuration for "fwsso_api"
        // (in /var/www/PSL/projects/clipper/app/config/parameters.yml).
        /*
        $this->authenticatedClient->request('GET', $uri, array('status' => 'order_pending'));
        $content = $this->authenticatedClient->getResponse()->getContent();
        */
    }

    public function testGetOrderAction()
    {
        $uri = $this->getUrl('get_order', array('uuid' => 123));
        $this->assertBehindFirewall('GET', $uri);

        $this->authenticatedClient->request('GET', $uri);
        $content = $this->authenticatedClient->getResponse()->getContent();
        $content = json_decode($content, true);

        // Assert invalid uuid.
        $this->assertEquals(
            array(
                'content' => 'No order with this id: 123',
                'status' => 204,
                'headers' => array(),
            ),
            $content
        );

        // Assert valid uuid.
        $records = $this
            ->getObjectManager()
            ->getRepository('\PSL\ClipperBundle\Entity\FirstQGroup')
            ->findAll();
        $record = $records[0];

        $uri = $this->getUrl('get_order', array('uuid' => $record->getId()));
        $this->authenticatedClient->request('GET', $uri);
        $content = $this->authenticatedClient->getResponse()->getContent();
        $content = json_decode($content, true);

        $this->assertEquals(
            $record->getId(),
            $content['content']['id']
        );
    }

    public function testPostOrderProcessAction()
    {
        $uri = $this->getUrl('post_order_process');
        $this->assertBehindFirewall('POST', $uri);

        // Assesrt post without params.
        $this->authenticatedClient->request('POST', $uri);
        $content = $this->authenticatedClient->getResponse()->getContent();
        $content = json_decode($content, true);

        $this->assertEquals(
            array(
                'content' => 'Invalid request - missing parameters',
                'status' => 400,
                'headers' => array(),
            ),
            $content
        );

        // Assert invalid FirstQ uuid.
        $postData = array('firstq_uuid' => 123, 'stripeToken' => 123, 'amount' => 123, 'email' => 'a@b.c');
        $postData = json_encode($postData);

        $this->authenticatedClient->request('POST', $uri, array(), array(), array('CONTENT_TYPE' => 'application/json'), $postData);
        $content = $this->authenticatedClient->getResponse()->getContent();
        $content = json_decode($content, true);

        $this->assertEquals(
            array(
                'content' => 'Error - FirstQ uuid is invalid',
                'status' => 400,
                'headers' => array(),
            ),
            $content
        );

        // Todo:
        // Assert payment system error.
        // Assert order complete.
        // Assert Card was declined.
        // Assert Network problem, perhaps try again.
        // Assert Invalid request
        // Assert Network problem, perhaps try again.
        // Assert Error - Please try again.
    }

    public function testPostOrderAdminprocessActionNoAuthentication()
    {
        $uri = $this->getUrl('post_order_adminprocess');
        $this->assertBehindFirewall('POST', $uri);
    }

    /**
     * @dataProvider postOrderAdminprocessActionDataProvider
     */
    public function testPostOrderAdminprocessAction($firstq_uuid, $task, $result, $state)
    {
        $uri = $this->getUrl('post_order_adminprocess');

        $postData = array();
        if ($firstq_uuid && $task) {
            $postData = array('firstq_uuid' => $firstq_uuid, 'task' => $task);
        }
        $postData = json_encode($postData);

        $this->authenticatedClient->request('POST', $uri, array(), array(), array('CONTENT_TYPE' => 'application/json'), $postData);
        $content = $this->authenticatedClient->getResponse()->getContent();
        $content = json_decode($content, true);

        if (!$state) {
            $this->assertEquals($result, $content);
        } else {
            $record = $this
                ->getObjectManager()
                ->getRepository('\PSL\ClipperBundle\Entity\FirstQGroup')
                ->find($firstq_uuid);
            $this->assertNotNull($record);
            $this->assertEquals($state, $record->getState());
        }
    }

    public function postOrderAdminprocessActionDataProvider()
    {
        // Reset data.
        $this->reloadFixture();

        $record = $this
            ->getObjectManager()
            ->getRepository('\PSL\ClipperBundle\Entity\FirstQGroup')
            ->findOneBy(array('state' => 'ORDER_INVOICE'));

        return array(
            'Without params' => array(
                null,
                null,
                array(
                    'content' => 'Invalid request - missing parameters',
                    'status' => 400,
                    'headers' => array(),
                ),
                null,
            ),
            'Invalid firstq_uuid' => array(
                123,
                'accept',
                array(
                    'content' => 'Error - FirstQ uuid is invalid',
                    'status' => 400,
                    'headers' => array(),
                ),
                null,
            ),
            'Valid firstq_uuid 1' => array(
                $record->getId(),
                'accept',
                array(),
                'ORDER_COMPLETE',
            ),
            'Valid firstq_uuid 2' => array(
                $record->getId(),
                'other',
                array(),
                'ORDER_DECLINED',
            ),
        );
    }

    public function testExitAction()
    {
        $uri = $this->getUrl('psl_clipper_exit');
        $crawler = $this->client->request('GET', $uri, array('lstoken' => 123));

        $this->assertEquals(
            1,
            $crawler->filter('p:contains("Thanks for completing the survey.")')->count()
        );

        $this->assertEquals(
            1,
            $crawler->filter('small:contains("Participant token: 123.")')->count()
        );

        $this->assertEquals(
            'http://habcentral.habcommunity.com/',
            $crawler->selectLink('')->link()->getUri()
        );
    }

    public function testThankyouAction()
    {
        $uri = $this->getUrl('psl_clipper_thankyou', array('fquuid' => 123));
        $crawler = $this->client->request('GET', $uri);

        $this->assertEquals(
            'Error - FirstQ uuid is invalid',
            $this->client->getResponse()->getContent()
        );

        // Todo: try to find a valid fquuid and assert the redirection.
    }

    public function testRedirectLimeSurveyAction()
    {
        $parameters = array(
            'sid' => 123,
            'slug' => 123,
            'lang' => 123,
        );
        $uri = $this->getUrl('psl_clipper_limesurvey_redirect', $parameters);
        $crawler = $this->client->request('GET', $uri);

        $this->assertEquals(
            'Redirecting to http://dev-limesurvey.pslgroup.com/index.php/survey/index/sid/123/token/123/lang/en',
            $crawler->filter('title')->text()
        );

        // Todo: Assert expires response.
    }
}
