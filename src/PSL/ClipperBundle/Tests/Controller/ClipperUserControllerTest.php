<?php

// src/PSL/ClipperBundle/Tests/Controller/ClipperUserControllerTest.php

namespace PSL\ClipperBundle\Tests\Controller;

use PSL\ClipperBundle\Tests\WebTestCase;

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
        $uri = $this->getUrl('get_user', array('uid' => 1));
        $this->assertBehindFirewall('GET', $uri);

        $this->authenticatedClient->request('GET', $uri);
        $content = $this->authenticatedClient->getResponse()->getContent();
        $content = json_decode($content, true);
    }

    public function testGetUserPasswordAction()
    {

    }
}
