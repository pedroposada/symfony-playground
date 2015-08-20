<?php

// src/PSL/ClipperBundle/Tests/Controller/ClipperUserControllerTest.php

namespace PSL\ClipperBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * ClipperUserControllerTest test.
 */
class ClipperUserControllerTest extends WebTestCase
{
    public function testPostUsersAction()
    {

    }

    public function testPostUserAction()
    {

    }

    public function testGetUserAction()
    {
        $client = static::createClient();
        $parameters = array('uid' => 1);
        $uri = $client->getContainer()->get('router')->generate('get_user', $parameters);
        $client->request('GET', $uri);

        // Assert authentication is needed.
        $this->assertAuthentication($client);
    }

    public function testGetUserPasswordAction()
    {

    }

    public function assertAuthentication($client)
    {
        // Decode response content data.
        $content = json_decode($client->getResponse()->getContent(), true);

        // Assert that authentication is needed.
        $this->assertEquals(
            array(
                'code' => 401,
                'message' => 'Invalid credentials',
            ),
            $content
        );
    }
}
