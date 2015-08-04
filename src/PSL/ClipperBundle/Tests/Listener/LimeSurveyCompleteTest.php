<?php

namespace PSL\ClipperBundle\Tests\Listener;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use PSL\ClipperBundle\Listener\LimeSurveyComplete;
use PSL\ClipperBundle\Event\FirstQProjectEvent;

class LimeSurveyCompleteTest extends WebTestCase
{
    public function testMain()
    {
      $client = static::createClient();
      
      $container = $client->getContainer();
      $kernel = $client->getKernel();      
      
      $params = $container->getParameter('clipper');
      $em = $container->get('doctrine')->getManager();
      
      $fqgs = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQGroup')->findByState($params['state_codes']['order_complete']);
      $this->assertNotEmpty($fqgs);
      $fqg = $fqgs->first();
      
      $fqps = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQProject')->findByFirstQGroupUUID($fqg->getId());
      $this->assertNotEmpty($fqps);
      $fqp = $fqps->first();
      
      $dispatcher = $container->get('event_dispatcher'); 
      $event = new FirstQProjectEvent($fqg, $fqp);
      
      $lsc = new LimeSurveyComplete($container, 'limesurvey_complete');
      
      $lsc->onMain($event, 'limesurvey_complete', $dispatcher);
      
      $this->assertSame($params['state_codes']['limesurvey_complete'], $fqp->getState());
      
    }
}