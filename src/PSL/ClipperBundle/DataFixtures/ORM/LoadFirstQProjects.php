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
      $fq1->setBcOrderId(1);
      $fq1->setBcProductId(76);
      $fq1->setFormDataRaw("Maecenas faucibus mollis interdum. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.");
      $fq1->setSheetDataRaw("Etiam porta sem malesuada magna mollis euismod. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor.");
      $fq1->setState("BIGCOMMERCE_PENDING");
      $manager->persist($fq1);
      
      $fq2 = new FirstQProject();
      $fq2->setBcOrderId(1);
      $fq2->setBcProductId(47);
      $fq2->setFormDataRaw("Vestibulum id ligula porta felis euismod semper.");
      $fq2->setSheetDataRaw("Donec sed odio dui.");
      $fq2->setState("BIGCOMMERCE_COMPLETE");
      $manager->persist($fq2);
      
      $fq3 = new FirstQProject();
      $fq3->setBcOrderId(3);
      $fq3->setBcProductId(3);
      $fq3->setFormDataRaw("Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Praesent commodo cursus magna, vel scelerisque nisl consectetur et.");
      $fq3->setSheetDataRaw("Curabitur blandit tempus porttitor.");
      $fq3->setState("LIMESURVEY_CREATED");
      $manager->persist($fq3);
      
      $fq4 = new FirstQProject();
      $fq4->setBcOrderId(4);
      $fq4->setBcProductId(4);
      $fq4->setFormDataRaw("Nulla vitae elit libero, a pharetra augue.ectetur et.");
      $fq4->setSheetDataRaw("Cras justo odio, dapibus ac facilisis in, egestas eget quam.");
      $fq4->setState("RPANEL_COMPLETE");
      $manager->persist($fq4);
      
      $fq5 = new FirstQProject();
      $fq5->setBcOrderId(5);
      $fq5->setBcProductId(5);
      $fq5->setFormDataRaw("Integer posuere erat a ante venenatis dapibus posuere velit aliquet.");
      $fq5->setSheetDataRaw("Cras justo odio, dapibus ac facilisis in, egestas eget quam.");
      $fq5->setState("LIMESURVEY_COMPLETE");
      $manager->persist($fq5);
      
      $fq6 = new FirstQProject();
      $fq6->setBcOrderId(6);
      $fq6->setBcProductId(6);
      $fq6->setFormDataRaw("Sed posuere consectetur est at lobortis.");
      $fq6->setSheetDataRaw("Praesent commodo cursus magna, vel scelerisque nisl consectetur et.");
      $fq6->setState("EMAIL_SENT");
      $manager->persist($fq6);
      
      $manager->flush();
    }
}