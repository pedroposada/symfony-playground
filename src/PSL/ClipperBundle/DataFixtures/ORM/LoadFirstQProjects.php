<?php

namespace PSL\ClipperBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PSL\ClipperBundle\Entity\FirstQGroup;
use PSL\ClipperBundle\Entity\FirstQProject;

class LoadFirstQProjects extends AbstractFixture implements OrderedFixtureInterface
{
  /**
   * {@inheritDoc}
   */
  public function load(ObjectManager $manager)
  {
    $fq1 = new FirstQProject();
    $fq1->setSheetDataRaw('{"market":"USA","specialty":"Oncology","feasibility":true,"participants_sample":"2500","price":"114,330","result":{"F3":"6,361","F5":"10,537","F7":"50","F8":"2500","F10":"0","F12":"GBP","F14":"114584","F15":"57,292","F16":"57,038","F17":"114,330","F20":"0","F21":"$0.00","F22":"$0.00","F24":"114,330","F26":"$18.00","F27":"USD"}}');
    $fq1->setState("LIMESURVEY_PENDING");
    $fq1->setFirstqgroup($this->getReference('firstqgroup'));
    $manager->persist($fq1);
    
    $fq2 = new FirstQProject();
    $fq2->setSheetDataRaw('{"market":"USA","specialty":"Cardiology","feasibility":true,"participants_sample":"2500","price":"114,330","result":{"F3":"25,541","F5":"34,528","F7":"50","F8":"2500","F10":"0","F12":"GBP","F14":"114584","F15":"57,292","F16":"57,038","F17":"114,330","F20":"0","F21":"$0.00","F22":"$0.00","F24":"114,330","F26":"$18.00","F27":"USD"}}');
    $fq2->setState("LIMESURVEY_PENDING");
    $fq2->setFirstqgroup($this->getReference('firstqgroup'));
    $manager->persist($fq2);
    
    $manager->flush();
  }

  /**
  * {@inheritDoc}
  */
  public function getOrder()
  {
      return 2; // the order in which fixtures will be loaded
  }
}
