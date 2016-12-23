<?php

// src/PP/SampleBundle/Tests/Listener/LimeSurveyPendingTest.php

namespace PP\SampleBundle\Tests\Listener;

use PP\SampleBundle\Tests\WebTestCase;
use PP\SampleBundle\Listener\LimeSurveyPending;
use PP\SampleBundle\Event\FirstQProjectEvent;
use Doctrine\Common\Collections\ArrayCollection;

class LimeSurveyPendingTest extends WebTestCase
{
    /**
     * @var PP\SampleBundle\Event\FirstQProjectEvent
     */
    protected $firstQProjectEvent;

    /**
     * @var PP\SampleBundle\Listener\LimeSurveyPending
     */
    protected $limeSurveyPending;

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
        $firstQProject->setState($this->params['state_codes']['limesurvey_pending']);

        $this->firstQProjectEvent = new FirstQProjectEvent($firstQGroup, $firstQProject);

        $this->limeSurveyPending= new LimeSurveyPending($this->container, 'limesurvey_pending');
    }

    public function testOnMain()
    {
        // TODO: call function
        // Maybe need to add survey_type to LoadFirstQGroups.php
        // $response = $this->limeSurveyPending->onMain($this->firstQProjectEvent, 'limesurvey_pending', $this->dispatcher);

        // TODO: assert output

        // TODO: assert values in db (integration tests)

    }
}
