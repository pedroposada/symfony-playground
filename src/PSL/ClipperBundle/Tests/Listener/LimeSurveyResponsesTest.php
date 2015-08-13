<?php

// phpunit -c app src/PSL/ClipperBundle/Tests/Listener/LimeSurveyResponsesTest.php

namespace PSL\ClipperBundle\Tests\Listener;

use \Exception as Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use PSL\ClipperBundle\Listener\LimeSurveyResponses as LimeSurveyResponses;
use PSL\ClipperBundle\Event\FirstQProjectEvent;
use PSL\ClipperBundle\ClipperEvents as ClipperEvents;
use Doctrine\Common\Collections\ArrayCollection;

class LimeSurveyResponsesTest extends WebTestCase
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
  
  public function testRefreshResponses()
  {
    // TODO: prepare input
    $fqgs = $this->em->getRepository('PSLClipperBundle:FirstQGroup')->findByState($this->params['state_codes']['order_complete']);
    $fqg = $fqgs->first();
    $fqps = $this->em->getRepository('PSLClipperBundle:FirstQProject')->findByFirstqgroup($fqg);
    $fqps = new ArrayCollection($fqps);
    $fqp = $fqps->first();
    $event = new FirstQProjectEvent($fqg, $fqp);
    $lsresps = new LimeSurveyResponses($this->container);
    
    // TODO: call function
    
    // TODO: assert output
    
    // TODO: assert values in db (integration tests)
    
  }
  
  /**
   * @expectedException Exception
   */
  public function testExceptions()
  {
    // TODO: prepare input
    $fqgs = $this->em->getRepository('PSLClipperBundle:FirstQGroup')->findByState($this->params['state_codes']['order_complete']);
    $fqg = $fqgs->first();
    $fqps = $this->em->getRepository('PSLClipperBundle:FirstQProject')->findByFirstqgroup($fqg);
    $fqps = new ArrayCollection($fqps);
    $fqp = $fqps->first();
    $event = new FirstQProjectEvent($fqg, $fqp);
    $lsresps = new LimeSurveyResponses($this->container);
    
    // TODO: call function
    $lsresps->fetchResponses('123'); // this is supposed to throw and Exception
    
  }
}