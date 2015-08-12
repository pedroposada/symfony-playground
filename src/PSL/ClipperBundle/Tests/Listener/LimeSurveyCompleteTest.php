<?php

// phpunit -c app src/PSL/ClipperBundle/Tests/Listener/LimeSurveyCompleteTest.php

namespace PSL\ClipperBundle\Tests\Listener;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use PSL\ClipperBundle\Listener\LimeSurveyComplete;
use PSL\ClipperBundle\Event\FirstQProjectEvent;

class LimeSurveyCompleteTest extends WebTestCase
{
  protected $client;
  protected $container;
  protected $params;
  protected $em;
  protected $dispatcher;

  public function __construct()
  {
    $this->client = static::createClient();
    $this->container = $this->client->getContainer();
    $this->params = $this->container->getParameter('clipper');
    $this->em = $this->container->get('doctrine')->getManager();
    $this->dispatcher = $this->container->get('event_dispatcher');
  }

  public function testOnMain()
  {
    // TODO: prepare input
    $fqgs = $this->em->getRepository('PSLClipperBundle:FirstQGroup')->findByState($this->params['state_codes']['order_complete']);
    $fqg = $fqgs->first();
    $fqps = $this->em->getRepository('PSLClipperBundle:FirstQProject')->findByFirstqgroup($fqg);
    $fqps = new ArrayCollection($fqps);
    $fqp = $fqps->first();
    $fqp->setState($this->params['state_codes']['limesurvey_complete']);
    $event = new FirstQProjectEvent($fqg, $fqp);
    $lsc = new LimeSurveyComplete($this->container, 'limesurvey_complete');
    
    // TODO: call function
    $lsc->onMain($event, 'limesurvey_complete', $this->dispatcher);
    
    // TODO: assert output
    
    // TODO: assert values in db (integration tests)
    
  }

  /**
   * @expectedException Exception
   */
  // public function testExcetptions()
  // {
  // }

}
