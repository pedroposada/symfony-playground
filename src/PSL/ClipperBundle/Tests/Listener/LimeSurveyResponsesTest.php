<?php

// src/PSL/ClipperBundle/Tests/Listener/LimeSurveyResponsesTest.php

namespace PSL\ClipperBundle\Tests\Listener;

use PSL\ClipperBundle\Tests\WebTestCase;
use PSL\ClipperBundle\Listener\LimeSurveyResponses;
use PSL\ClipperBundle\Event\FirstQProjectEvent;

class LimeSurveyResponsesTest extends WebTestCase
{
    /**
     * @var PSL\ClipperBundle\Event\FirstQProjectEvent
     */
    protected $firstQProjectEvent;

    /**
     * @var PSL\ClipperBundle\Listener\LimeSurveyResponses
     */
    protected $limeSurveyResponses;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $firstQGroups = $this
            ->getObjectManager()
            ->getRepository('\PSL\ClipperBundle\Entity\FirstQGroup')
            ->findByState($this->params['state_codes']['order_complete']);
        $firstQGroup = $firstQGroups->first();

        $firstQProjects = $this
            ->getObjectManager()
            ->getRepository('\PSL\ClipperBundle\Entity\FirstQProject')
            ->findByFirstqgroupAndNotState($firstQGroup, $this->params['state_codes']['order_complete']);
        $firstQProject = $firstQProjects->first();
        $firstQProject->setState($this->params['state_codes']['limesurvey_complete']);

        $this->firstQProjectEvent = new FirstQProjectEvent($firstQGroup, $firstQProject);
        $this->limeSurveyResponses= new LimeSurveyResponses($this->container);
    }

    public function testRefreshResponses()
    {
        // TODO: call function
        // $this->limeSurveyResponses->refreshResponses($this->firstQProjectEvent, 'event-name', $this->container->get('event_dispatcher'));

        // TODO: assert output

        // TODO: assert values in db (integration tests)
    }

    /**
     * @dataProvider fetchResponsesProvider
     */
    public function testFetchResponses($iSurveyID, $expectedResponse)
    {
        // TODO: call function
        // $response = $this->limeSurveyResponses->fetchResponses($iSurveyID);

        // TODO: assert output
        // $this->assertEquals($expectedResponse, $response);

        // TODO: assert values in db (integration tests)
    }

    public function fetchResponsesProvider()
    {
        return array(
            'invalid survey id' => array(
                123,
                new \Exception
            ),
            'valid survey id' => array(
                456,
                new \Exception
            ),
        );
    }

    public function testSaveResponses()
    {
        // TODO: call function
        // $this->limeSurveyResponses->saveResponses();

        // TODO: assert output

        // TODO: assert values in db (integration tests)
    }
}
