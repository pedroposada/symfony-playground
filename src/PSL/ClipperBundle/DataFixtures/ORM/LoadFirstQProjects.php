<?php

namespace PSL\ClipperBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PSL\ClipperBundle\Entity\FirstQProject;

class LoadFirstQProjects implements FixtureInterface
{
  /**
   * {@inheritDoc}
   */
  public function load(ObjectManager $manager)
  {

    $fq1 = new FirstQProject();
    $fq1->setBcProductId(76);
    $fq1->setFormDataRaw('O:8:"stdClass":10:{s:3:"loi";i:10;s:2:"ir";i:10;s:5:"title";s:4:"aaaa";s:4:"name";s:3:"FQ2";s:12:"patient_type";s:4:"bbbb";s:16:"num_participants";s:2:"13";s:6:"market";s:7:"Germany";s:9:"specialty";s:10:"Psychiatry";s:9:"timestamp";s:13:"1433794510596";s:5:"brand";a:4:{i:0;s:2:"cc";i:1;s:2:"dd";i:2;s:2:"ee";i:3;s:2:"ff";}}');
    $fq1->setSheetDataRaw("Etiam porta sem malesuada magna mollis euismod. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor.");
    $fq1->setState("BIGCOMMERCE_PENDING");
    $manager->persist($fq1);

    $fq2 = new FirstQProject();
    $fq2->setBcProductId(47);
    $fq2->setFormDataRaw("Vestibulum id ligula porta felis euismod semper.");
    $fq2->setSheetDataRaw("Donec sed odio dui.");
    $fq2->setState("BIGCOMMERCE_COMPLETE");
    $manager->persist($fq2);

    $fq3 = new FirstQProject();
    $fq3->setBcProductId(3);
    $fq3->setFormDataRaw("Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Praesent commodo cursus magna, vel scelerisque nisl consectetur et.");
    $fq3->setSheetDataRaw("Curabitur blandit tempus porttitor.");
    $fq3->setState("LIMESURVEY_CREATED");
    $manager->persist($fq3);

    $fq4 = new FirstQProject();
    $fq4->setBcProductId(4);
    $fq4->setFormDataRaw("Nulla vitae elit libero, a pharetra augue.ectetur et.");
    $fq4->setSheetDataRaw("Cras justo odio, dapibus ac facilisis in, egestas eget quam.");
    $fq4->setState("RPANEL_COMPLETE");
    $manager->persist($fq4);

    $fq5 = new FirstQProject();
    $fq5->setBcProductId(5);
    $fq5->setFormDataRaw("Integer posuere erat a ante venenatis dapibus posuere velit aliquet.");
    $fq5->setSheetDataRaw("Cras justo odio, dapibus ac facilisis in, egestas eget quam.");
    $fq5->setState("LIMESURVEY_COMPLETE");
    $manager->persist($fq5);

    $fq6 = new FirstQProject();
    $fq6->setBcProductId(6);
    $fq6->setFormDataRaw("Sed posuere consectetur est at lobortis.");
    $fq6->setSheetDataRaw("Praesent commodo cursus magna, vel scelerisque nisl consectetur et.");
    $fq6->setState("EMAIL_SENT");
    $manager->persist($fq6);

    $manager->flush();
  }

}
