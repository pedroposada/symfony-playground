<?php

// src/PSL/ClipperBundle/Tests/Controller/ClipperUserControllerTest.php

namespace PSL\ClipperBundle\Tests\Controller;

use PSL\ClipperBundle\Tests\WebTestCase;

/**
 * ClipperUserControllerTest test.
 */
class ClipperUserControllerTest extends WebTestCase
{
    public function testPostNewuserAction()
    {
        $uri = $this->getUrl('post_newuser');

        $postData = array();

        $this->authenticatedClient->request('POST', $uri, array(), array(), array('CONTENT_TYPE' => 'application/json'), $postData);
        $content = $this->authenticatedClient->getResponse()->getContent();
        $content = json_decode($content, true);

        $this->assertEquals(
            'Request parameter "username" is empty',
            $content['content']['error_message']
        );

        $rand = time();

        $postData = array(
            'username' => 'user_' . $rand,
            'mail' => 'mail_' . $rand . '@example.com',
            'pass' => 'a pass',
            'firstname' => 'a firstname',
            'lastname' => ' a lastname',
            'country' => 'Canada',
            'company' => 'a company',
            'title' => 'a titla',
            'jobfunction' => '232', // 'Pharmacist' => 232
            'salutation' => null,
            'telephone' => 'a telephone',
        );
        $postData = json_encode($postData);

        $this->authenticatedClient->request('POST', $uri, array(), array(), array('CONTENT_TYPE' => 'application/json'), $postData);
        $content = $this->authenticatedClient->getResponse()->getContent();
        $content = json_decode($content, true);

        $this->assertTrue(is_array($content));
        $this->assertTrue(!empty($content));
        $this->assertTrue(is_array($content['content']));
        $this->assertTrue(!empty($content['content']));
        $this->assertTrue(is_array($content['content']['user']));
        $this->assertTrue(!empty($content['content']['user']));
        $this->assertEquals(
            'user_' . $rand,
            $content['content']['user']['name']
        );
        $this->assertEquals(
            'mail_' . $rand . '@example.com',
            $content['content']['user']['mail']
        );
    }

    /**
     * @dataProvider postUserActionProvider
     */
    public function testPostUserAction($postData, $result)
    {
        $uri = $this->getUrl('post_user', array('uid' => 250199));

        $this->authenticatedClient->request('POST', $uri, array(), array(), array('CONTENT_TYPE' => 'application/json'), $postData);
        $content = $this->authenticatedClient->getResponse()->getContent();
        $content = json_decode($content, true);

        $this->assertArrayHasKey('user', $content['content']);

        $this->assertEquals(
            $result['firstname'],
            $content['content']['user']['field_firstname']['und'][0]['value']
        );

        $this->assertEquals(
            $result['lastname'],
            $content['content']['user']['field_lastname']['und'][0]['value']
        );
    }

    public function postUserActionProvider()
    {
        return array(
            'User data set 1' => array(
                json_encode(array(
                    'username' => 'user_1440750926',
                    'mail' => 'mail_1440750926@example.com',
                    'pass' => '$S$DK9IIs2MEEZ15XmqmVGncP896.GpRoGmTxB/JuNDfX0Z2PxojaO9',
                    'firstname' => 'a firstname',
                    'lastname' => 'a lastname',
                    'country' => 'Canada',
                    'company' => 'a company',
                    'title' => 'a title',
                    'jobfunction' => 232,

                )),
                array(
                    'firstname' => 'a firstname',
                    'lastname' => 'a lastname',
                ),
            ),
            'User data set 2' => array(
                json_encode(array(
                    'username' => 'user_1440750926',
                    'mail' => 'mail_1440750926@example.com',
                    'pass' => '$S$DK9IIs2MEEZ15XmqmVGncP896.GpRoGmTxB/JuNDfX0Z2PxojaO9',
                    'firstname' => 'b firstname',
                    'lastname' => 'b lastname',
                    'country' => 'Canada',
                    'company' => 'a company',
                    'title' => 'a title',
                    'jobfunction' => 232,

                )),
                array(
                    'firstname' => 'b firstname',
                    'lastname' => 'b lastname',
                ),
            ),
            'User data set 3' => array(
                json_encode(array(
                    'username' => 'user_1440750926',
                    'mail' => 'mail_1440750926@example.com',
                    'pass' => '$S$DK9IIs2MEEZ15XmqmVGncP896.GpRoGmTxB/JuNDfX0Z2PxojaO9',
                    'firstname' => 'c firstname',
                    'lastname' => 'c lastname',
                    'country' => 'Canada',
                    'company' => 'a company',
                    'title' => 'a title',
                    'jobfunction' => 232,

                )),
                array(
                    'firstname' => 'c firstname',
                    'lastname' => 'c lastname',
                ),
            ),
            // Revert back to set 1.
            'User data set 4' => array(
                json_encode(array(
                    'username' => 'user_1440750926',
                    'mail' => 'mail_1440750926@example.com',
                    'pass' => '$S$DK9IIs2MEEZ15XmqmVGncP896.GpRoGmTxB/JuNDfX0Z2PxojaO9',
                    'firstname' => 'a firstname',
                    'lastname' => 'a lastname',
                    'country' => 'Canada',
                    'company' => 'a company',
                    'title' => 'a title',
                    'jobfunction' => 232,

                )),
                array(
                    'firstname' => 'a firstname',
                    'lastname' => 'a lastname',
                ),
            ),
        );
    }

    public function testGetUserAction()
    {
        $uri = $this->getUrl('get_user', array('uid' => 250199));
        $this->assertBehindFirewall('GET', $uri);

        $this->authenticatedClient->request('GET', $uri);
        $content = $this->authenticatedClient->getResponse()->getContent();
        $content = json_decode($content, true);

        $this->assertEquals(
            'a firstname',
            $content['content']['user']['field_firstname']['und'][0]['value']
        );

        $this->assertEquals(
            'a lastname',
            $content['content']['user']['field_lastname']['und'][0]['value']
        );
    }

    public function testGetUserPasswordAction()
    {
        $uri = $this->getUrl('get_user_password');
        $this->assertBehindFirewall('GET', $uri);

        $this->authenticatedClient->request('GET', $uri);
        $content = $this->authenticatedClient->getResponse()->getContent();
        $content = json_decode($content, true);

        $this->assertEquals(
            'An error has occurred. Please try again.',
            $content['content']['error_message']
        );

        $uri = $this->getUrl('get_user_password');
        $this->assertBehindFirewall('GET', $uri);

        $this->authenticatedClient->request('GET', $uri, array('email' => 'mail_1440750894@example.com'));
        $content = $this->authenticatedClient->getResponse()->getContent();
        $content = json_decode($content, true);

        $this->assertEquals(
            '["Password reset instructions mailed to mail_1440750894@example.com from API"]',
            $content['content']['message']['content']
        );
    }
}
