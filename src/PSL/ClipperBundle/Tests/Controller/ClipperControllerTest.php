<?php

// src/PSL/ClipperBundle/Tests/Controller/ClipperControllerTest.php

namespace PSL\ClipperBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

// use PSL\ClipperBundle\Controller\ClipperController;

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
    }

    public function autocompleteParametersProvider()
    {
        return array(
            // ?keyword=a
            'test' => array(array(
                'keyword' => 'a',
            )),
            // ?group=brands&keyword=a
            array(array(
                'group' => 'brands',
                'keyword' => 'a',
            )),
            // ?group=conditions&keyword=a
            array(array(
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
        // $client = static::createClient();
        // $uri = $client->getContainer()->get('router')->generate('post_order');
        // // $client->request('POST', $uri, array(), array(), array(), $postData);
        // $client->request('POST', $uri . '?' . $postData);
        // var_dump($client->getResponse()->getContent());
        // exit;
        // var_dump($uri);
        // exit;

        // $url =
        //
    }

    public function postOrderDataProvider()
    {
        return array(
            array('ir=20&name=a%20name&title=a%20title&name_full=a%20name%20full&patient_type=sick&timestamp=1436452135&market[]=USA&specialty[]=Oncology&specialty[]=Cardiology&survey_brand[]=AA-123&survey_brand[]=BB-456&survey_brand[]=CC-789&survey_brand[]=DD-123&survey_brand[]=EE-456&survey_brand[]=FF-789&attribute[]=it%20just%20works&attribute[]=painfull%20side%20effects&attribute[]=risk%20of%20death&attribute[]=just%20painful&attribute[]=mildly%20pointless&attribute[]=kind%20of%20cool&attribute[]=not%20effective&attribute[]=gives%20headaches&launch_date=2015-07-22%2011:10:33&timezone_client=Europe/London'),
        );
    }
}
