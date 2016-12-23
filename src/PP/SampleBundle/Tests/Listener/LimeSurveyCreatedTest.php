<?php

// src/PP/SampleBundle/Tests/Listener/LimeSurveyCreatedTest.php

namespace PP\SampleBundle\Tests\Listener;

use PP\SampleBundle\Tests\WebTestCase;
use PP\SampleBundle\Listener\LimeSurveyCreated;
use PP\SampleBundle\Event\FirstQProjectEvent;
use Doctrine\Common\Collections\ArrayCollection;
use PP\SampleBundle\Service\RPanelService;

class LimeSurveyCreatedTest extends WebTestCase
{
    /**
     * @var PP\SampleBundle\Event\FirstQProjectEvent
     */
    protected $firstQProjectEvent;

    /**
     * @var PP\SampleBundle\Listener\LimeSurveyCreated
     */
    protected $limeSurveyCreated;

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
        $firstQProject->setState($this->params['state_codes']['limesurvey_created']);

        $this->firstQProjectEvent = new FirstQProjectEvent($firstQGroup, $firstQProject);

        $rps = new RPanelService(array());
        $this->limeSurveyCreated= new LimeSurveyCreated($this->container, 'limesurvey_created', $rps);
    }

    public function testOnMain()
    {
        // TODO: call function
        // $response = $this->limeSurveyCreated->onMain($this->firstQProjectEvent, 'limesurvey_created', $this->dispatcher);

        // TODO: assert output

        // TODO: assert values in db (integration tests)

    }
}
