<?php

// src/PSL/ClipperBundle/Tests/Listener/LimeSurveyCreatedTest.php

namespace PSL\ClipperBundle\Tests\Listener;

use PSL\ClipperBundle\Tests\WebTestCase;
use PSL\ClipperBundle\Listener\LimeSurveyCreated;
use PSL\ClipperBundle\Event\FirstQProjectEvent;
use Doctrine\Common\Collections\ArrayCollection;
use PSL\ClipperBundle\Service\RPanelService;

class LimeSurveyCreatedTest extends WebTestCase
{
    /**
     * @var PSL\ClipperBundle\Event\FirstQProjectEvent
     */
    protected $firstQProjectEvent;

    /**
     * @var PSL\ClipperBundle\Listener\LimeSurveyCreated
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
            ->getRepository('\PSL\ClipperBundle\Entity\FirstQGroup')
            ->findByState($this->params['state_codes']['order_complete']);
        $firstQGroup = $firstQGroups->first();

        $firstQProjects = $this
            ->getObjectManager()
            ->getRepository('\PSL\ClipperBundle\Entity\FirstQProject')
            ->findByFirstqgroupAndNotState($firstQGroup, $this->params['state_codes']['order_complete']);
        $firstQProject = $firstQProjects->first();
        $firstQProject->setState($this->params['state_codes']['limesurvey_created']);

        $this->firstQProjectEvent = new FirstQProjectEvent($firstQGroup, $firstQProject);

        $rps = new RPanelService(array());
        $this->limeSurveyCreated= new LimeSurveyCreated($this->container, 'limesurvey_created', $rps);
        $user = $this->container->get('user_service')->getUserById('250348');
        $this->limeSurveyCreated->setUser($user);
    }

    public function testOnMain()
    {
        // TODO: call function
        // $response = $this->limeSurveyCreated->onMain($this->firstQProjectEvent, 'limesurvey_created', $this->dispatcher);

        // TODO: assert output

        // TODO: assert values in db (integration tests)

    }
}
