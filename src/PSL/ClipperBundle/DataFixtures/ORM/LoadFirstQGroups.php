<?php

namespace PSL\ClipperBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PSL\ClipperBundle\Entity\FirstQGroup;

class LoadFirstQGroups extends AbstractFixture implements OrderedFixtureInterface
{
  /**
   * {@inheritDoc}
   */
  public function load(ObjectManager $manager)
  {

    $ent1 = new FirstQGroup();
    $ent1->setFormDataRaw('{"loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","timezone_client":"Europe\/London"}');
    $ent1->setState('ORDER_COMPLETE');
    
    $manager->persist($ent1);
    $manager->flush();
    
    $this->addReference('firstqgroup', $ent1);
  }
  
  /**
   * {@inheritDoc}
   */
  public function getOrder()
  {
      return 1; // the order in which fixtures will be loaded
  }

}
