<?php

// src/PSL/ClipperBundle/Tests/Listener/LimeSurveyPendingTest.php

namespace PSL\ClipperBundle\Tests\Listener;

use PSL\ClipperBundle\Tests\WebTestCase;
use PSL\ClipperBundle\Listener\LimeSurveyPending;
use PSL\ClipperBundle\Event\FirstQProjectEvent;
use Doctrine\Common\Collections\ArrayCollection;

class LimeSurveyPendingTest extends WebTestCase
{
    /**
     * @var PSL\ClipperBundle\Event\FirstQProjectEvent
     */
    protected $firstQProjectEvent;

    /**
     * @var PSL\ClipperBundle\Listener\LimeSurveyPending
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
            ->getRepository('\PSL\ClipperBundle\Entity\FirstQGroup')
            ->findByState($this->params['state_codes']['order_complete']);
        $firstQGroup = $firstQGroups->first();

        $firstQProjects = $this
            ->getObjectManager()
            ->getRepository('\PSL\ClipperBundle\Entity\FirstQProject')
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
