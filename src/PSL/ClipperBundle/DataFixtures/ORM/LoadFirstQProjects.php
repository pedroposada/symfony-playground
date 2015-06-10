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
    $fq1->setSheetDataRaw('O:8:"stdClass":3:{s:11:"feasibility";b:1;s:11:"description";s:75:"Size of Universe Represented 6,361 - Percent of Universe Represented 37500%";s:6:"result";a:16:{s:2:"F3";s:5:"6,361";s:2:"F5";s:5:"8,866";s:2:"F7";s:2:"45";s:2:"F8";s:4:"2250";s:3:"F10";s:1:"0";s:3:"F12";s:3:"GBP";s:3:"F14";s:6:"232032";s:3:"F15";s:7:"116,016";s:3:"F16";s:6:"51,334";s:3:"F17";s:7:"167,350";s:3:"F20";s:1:"0";s:3:"F21";s:5:"$0.00";s:3:"F22";s:5:"$0.00";s:3:"F24";s:7:"167,350";s:3:"F26";s:6:"$41.00";s:3:"F27";s:3:"USD";}}');
    $fq1->setState("BIGCOMMERCE_PENDING");
    $manager->persist($fq1);

    $fq2 = new FirstQProject();
    $fq2->setBcProductId(47);
    $fq2->setFormDataRaw('O:8:"stdClass":10:{s:3:"loi";i:10;s:2:"ir";i:10;s:5:"title";s:13:"A survey name";s:4:"name";s:3:"FQ3";s:12:"patient_type";s:14:"A Patient type";s:16:"num_participants";i:35;s:6:"market";s:3:"USA";s:9:"specialty";s:10:"Cardiology";s:9:"timestamp";s:13:"1433963299113";s:5:"brand";a:4:{i:0;s:7:"Brand A";i:1;s:7:"Brand B";i:2;s:7:"Brand C";i:3;s:7:"Brand D";}}');
    $fq2->setSheetDataRaw('O:8:"stdClass":3:{s:11:"feasibility";b:1;s:11:"description";s:75:"Size of Universe Represented 6,361 - Percent of Universe Represented 37500%";s:6:"result";a:16:{s:2:"F3";s:5:"6,361";s:2:"F5";s:5:"8,866";s:2:"F7";s:2:"45";s:2:"F8";s:4:"2250";s:3:"F10";s:1:"0";s:3:"F12";s:3:"GBP";s:3:"F14";s:6:"232032";s:3:"F15";s:7:"116,016";s:3:"F16";s:6:"51,334";s:3:"F17";s:7:"167,350";s:3:"F20";s:1:"0";s:3:"F21";s:5:"$0.00";s:3:"F22";s:5:"$0.00";s:3:"F24";s:7:"167,350";s:3:"F26";s:6:"$41.00";s:3:"F27";s:3:"USD";}}');
    $fq2->setState("BIGCOMMERCE_COMPLETE");
    $manager->persist($fq2);

    $fq3 = new FirstQProject();
    $fq3->setBcProductId(3);
    $fq3->setFormDataRaw('O:8:"stdClass":10:{s:3:"loi";i:10;s:2:"ir";i:10;s:5:"title";s:13:"A survey name";s:4:"name";s:3:"FQ3";s:12:"patient_type";s:14:"A Patient type";s:16:"num_participants";i:35;s:6:"market";s:3:"USA";s:9:"specialty";s:10:"Cardiology";s:9:"timestamp";s:13:"1433963299113";s:5:"brand";a:4:{i:0;s:7:"Brand A";i:1;s:7:"Brand B";i:2;s:7:"Brand C";i:3;s:7:"Brand D";}}');
    $fq3->setSheetDataRaw('O:8:"stdClass":3:{s:11:"feasibility";b:1;s:11:"description";s:75:"Size of Universe Represented 6,361 - Percent of Universe Represented 37500%";s:6:"result";a:16:{s:2:"F3";s:5:"6,361";s:2:"F5";s:5:"8,866";s:2:"F7";s:2:"45";s:2:"F8";s:4:"2250";s:3:"F10";s:1:"0";s:3:"F12";s:3:"GBP";s:3:"F14";s:6:"232032";s:3:"F15";s:7:"116,016";s:3:"F16";s:6:"51,334";s:3:"F17";s:7:"167,350";s:3:"F20";s:1:"0";s:3:"F21";s:5:"$0.00";s:3:"F22";s:5:"$0.00";s:3:"F24";s:7:"167,350";s:3:"F26";s:6:"$41.00";s:3:"F27";s:3:"USD";}}');
    $fq3->setState("LIMESURVEY_CREATED");
    $manager->persist($fq3);

    $fq4 = new FirstQProject();
    $fq4->setBcProductId(4);
    $fq4->setFormDataRaw('O:8:"stdClass":10:{s:3:"loi";i:10;s:2:"ir";i:10;s:5:"title";s:13:"A survey name";s:4:"name";s:3:"FQ3";s:12:"patient_type";s:14:"A Patient type";s:16:"num_participants";i:35;s:6:"market";s:3:"USA";s:9:"specialty";s:10:"Cardiology";s:9:"timestamp";s:13:"1433963299113";s:5:"brand";a:4:{i:0;s:7:"Brand A";i:1;s:7:"Brand B";i:2;s:7:"Brand C";i:3;s:7:"Brand D";}}');
    $fq4->setSheetDataRaw('O:8:"stdClass":3:{s:11:"feasibility";b:1;s:11:"description";s:75:"Size of Universe Represented 6,361 - Percent of Universe Represented 37500%";s:6:"result";a:16:{s:2:"F3";s:5:"6,361";s:2:"F5";s:5:"8,866";s:2:"F7";s:2:"45";s:2:"F8";s:4:"2250";s:3:"F10";s:1:"0";s:3:"F12";s:3:"GBP";s:3:"F14";s:6:"232032";s:3:"F15";s:7:"116,016";s:3:"F16";s:6:"51,334";s:3:"F17";s:7:"167,350";s:3:"F20";s:1:"0";s:3:"F21";s:5:"$0.00";s:3:"F22";s:5:"$0.00";s:3:"F24";s:7:"167,350";s:3:"F26";s:6:"$41.00";s:3:"F27";s:3:"USD";}}');
    $fq4->setState("RPANEL_COMPLETE");
    $manager->persist($fq4);

    $fq5 = new FirstQProject();
    $fq5->setBcProductId(5);
    $fq5->setFormDataRaw('O:8:"stdClass":10:{s:3:"loi";i:10;s:2:"ir";i:10;s:5:"title";s:13:"A survey name";s:4:"name";s:3:"FQ3";s:12:"patient_type";s:14:"A Patient type";s:16:"num_participants";i:35;s:6:"market";s:3:"USA";s:9:"specialty";s:10:"Cardiology";s:9:"timestamp";s:13:"1433963299113";s:5:"brand";a:4:{i:0;s:7:"Brand A";i:1;s:7:"Brand B";i:2;s:7:"Brand C";i:3;s:7:"Brand D";}}');
    $fq5->setSheetDataRaw('O:8:"stdClass":3:{s:11:"feasibility";b:1;s:11:"description";s:75:"Size of Universe Represented 6,361 - Percent of Universe Represented 37500%";s:6:"result";a:16:{s:2:"F3";s:5:"6,361";s:2:"F5";s:5:"8,866";s:2:"F7";s:2:"45";s:2:"F8";s:4:"2250";s:3:"F10";s:1:"0";s:3:"F12";s:3:"GBP";s:3:"F14";s:6:"232032";s:3:"F15";s:7:"116,016";s:3:"F16";s:6:"51,334";s:3:"F17";s:7:"167,350";s:3:"F20";s:1:"0";s:3:"F21";s:5:"$0.00";s:3:"F22";s:5:"$0.00";s:3:"F24";s:7:"167,350";s:3:"F26";s:6:"$41.00";s:3:"F27";s:3:"USD";}}');
    $fq5->setState("LIMESURVEY_COMPLETE");
    $manager->persist($fq5);

    $fq6 = new FirstQProject();
    $fq6->setBcProductId(6);
    $fq6->setFormDataRaw('O:8:"stdClass":10:{s:3:"loi";i:10;s:2:"ir";i:10;s:5:"title";s:13:"A survey name";s:4:"name";s:3:"FQ3";s:12:"patient_type";s:14:"A Patient type";s:16:"num_participants";i:35;s:6:"market";s:3:"USA";s:9:"specialty";s:10:"Cardiology";s:9:"timestamp";s:13:"1433963299113";s:5:"brand";a:4:{i:0;s:7:"Brand A";i:1;s:7:"Brand B";i:2;s:7:"Brand C";i:3;s:7:"Brand D";}}');
    $fq6->setSheetDataRaw('O:8:"stdClass":3:{s:11:"feasibility";b:1;s:11:"description";s:75:"Size of Universe Represented 6,361 - Percent of Universe Represented 37500%";s:6:"result";a:16:{s:2:"F3";s:5:"6,361";s:2:"F5";s:5:"8,866";s:2:"F7";s:2:"45";s:2:"F8";s:4:"2250";s:3:"F10";s:1:"0";s:3:"F12";s:3:"GBP";s:3:"F14";s:6:"232032";s:3:"F15";s:7:"116,016";s:3:"F16";s:6:"51,334";s:3:"F17";s:7:"167,350";s:3:"F20";s:1:"0";s:3:"F21";s:5:"$0.00";s:3:"F22";s:5:"$0.00";s:3:"F24";s:7:"167,350";s:3:"F26";s:6:"$41.00";s:3:"F27";s:3:"USD";}}');
    $fq6->setState("EMAIL_SENT");
    $manager->persist($fq6);

    $manager->flush();
  }

}
