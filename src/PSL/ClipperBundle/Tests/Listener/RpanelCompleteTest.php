<?php

// src/PSL/ClipperBundle/Tests/Listener/RpanelCompleteTest.php

namespace PSL\ClipperBundle\Tests\Listener;

use PSL\ClipperBundle\Tests\WebTestCase;
use PSL\ClipperBundle\Listener\RpanelComplete;
use PSL\ClipperBundle\Event\FirstQProjectEvent;
use Doctrine\Common\Collections\ArrayCollection;

class RpanelCompleteTest extends WebTestCase
{
    protected $client;
    protected $container;
    protected $params;
    protected $dispatcher;

    public function __construct()
    {
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->params = $this->container->getParameter('clipper');
        $this->dispatcher = $this->container->get('event_dispatcher');
    }

    public function testOnMain()
    {
        // TODO: prepare input
        $state = $this->params['state_codes']['order_complete'];

        $firstQGroups = $this
            ->getObjectManager()
            ->getRepository('\PSL\ClipperBundle\Entity\FirstQGroup')
            ->findByState($state);
        $firstQGroup = $firstQGroups->first();

        $firstQProjects = $this
            ->getObjectManager()
            ->getRepository('\PSL\ClipperBundle\Entity\FirstQProject')
            ->findByFirstqgroupAndNotState($firstQGroup, $state);
        $firstQProject = $firstQProjects->first();
        $firstQProject->setState($this->params['state_codes']['rpanel_complete']);

        $firtQProjectEvent = new FirstQProjectEvent($firstQGroup, $firstQProject);
        $rpanelComplete = new RpanelComplete($this->container, 'rpanel_complete');


        // TODO: call function
        // Exception: Bad response from LimeSurvey with status [Invalid surveyid] for fqp->id: [29DE82F4-6543-4ACD-AF55-0A76E7E51841] on [get_summary]
        // $rpanelComplete->onMain($firtQProjectEvent, 'rpanel_complete', $this->dispatcher);

        // TODO: assert output

        // TODO: assert values in db (integration tests)

    }
}
