<?php

// src/PSL/ClipperBundle/Tests/Controller/ChartsControllerTest.php

namespace PSL\ClipperBundle\Tests\Controller;

use PSL\ClipperBundle\Tests\WebTestCase;

/**
 * ChartsControllerTest test.
 */
class ChartsControllerTest extends WebTestCase
{
    public function testChartsAction()
    {
        $uri = $this->getUrl('psl_clipper_chart');

        $parameters = array(
            'order_id' => '',
            'params' => array(),
        );
        $this->client->request('GET', $uri, $parameters);
    }
}
