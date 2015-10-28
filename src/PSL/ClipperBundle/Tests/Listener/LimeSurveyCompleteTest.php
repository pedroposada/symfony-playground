<?php

// src/PSL/ClipperBundle/Tests/Listener/LimeSurveyCompleteTest.php

namespace PSL\ClipperBundle\Tests\Listener;

use PSL\ClipperBundle\Tests\WebTestCase;
use PSL\ClipperBundle\Listener\LimeSurveyComplete;
use PSL\ClipperBundle\Event\FirstQProjectEvent;

class LimeSurveyCompleteTest extends WebTestCase
{
    /**
     * @var PSL\ClipperBundle\Event\FirstQProjectEvent
     */
    protected $firstQProjectEvent;

    /**
     * @var PSL\ClipperBundle\Listener\LimeSurveyResponses
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
        $this->limeSurveyComplete = new LimeSurveyComplete($this->container, 'limesurvey_complete');
    }

    public function testOnMain()
    {
        // TODO: call function
        // Fatal error: Call to protected method PSL\ClipperBundle\Listener\LimeSurveyComplete::main()
        // $response = $this->limeSurveyComplete->main($this->firstQProjectEvent, 'limesurvey_complete', $this->dispatcher);

        // TODO: assert output

        // TODO: assert values in db (integration tests)

    }
}
