<?php

// src/PP/SampleBundle/Tests/Listener/LimeSurveyCompleteTest.php

namespace PP\SampleBundle\Tests\Listener;

use PP\SampleBundle\Tests\WebTestCase;
use PP\SampleBundle\Listener\LimeSurveyComplete;
use PP\SampleBundle\Event\FirstQProjectEvent;

class LimeSurveyCompleteTest extends WebTestCase
{
    /**
     * @var PP\SampleBundle\Event\FirstQProjectEvent
     */
    protected $firstQProjectEvent;

    /**
     * @var PP\SampleBundle\Listener\LimeSurveyResponses
     */
    protected $limeSurveyComplete;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $firstQGroups = $this
            ->getObjectManager()
            ->getRepository('\PP\SampleBundle\Entity\FirstQGroup')
            ->findByState($this->params['state_codes']['order_complete']);
        $firstQGroup = $firstQGroups->first();

        $firstQProjects = $this
            ->getObjectManager()
            ->getRepository('\PP\SampleBundle\Entity\FirstQProject')
            ->findByFirstqgroupAndNotState($firstQGroup, $this->params['state_codes']['order_complete']);
        $firstQProject = $firstQProjects->first();
        $firstQProject->setState($this->params['state_codes']['limesurvey_complete']);

        $this->firstQProjectEvent = new FirstQProjectEvent($firstQGroup, $firstQProject);

        $this->limeSurveyComplete = new LimeSurveyComplete($this->container, 'limesurvey_complete');
        $user_service = $this->container->get('user_service');
        $user = $user_service->findById('250348');
        $this->limeSurveyComplete->setUser($user);
    }

    public function testOnMain()
    {
        // TODO: call function
        $response = $this->limeSurveyComplete->main($this->firstQProjectEvent, 'limesurvey_complete', $this->dispatcher);

        // TODO: assert output

        // TODO: assert values in db (integration tests)

    }
}
