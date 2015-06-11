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
    $fq1->setLimesurveyDataRaw('a:2:{s:3:"sid";i:666557;s:4:"urls";a:13:{i:0;s:84:"http://localhost:8000/clipper/limesurvey/666557/419266f0-d368-46d2-9c4b-3accd0e91e97";i:1;s:84:"http://localhost:8000/clipper/limesurvey/666557/80e2a623-0c75-41f0-8bf1-e28a62e597cb";i:2;s:84:"http://localhost:8000/clipper/limesurvey/666557/37dbfee7-fe7b-47c8-ae82-23cbfa36f6c5";i:3;s:84:"http://localhost:8000/clipper/limesurvey/666557/d9d6c675-ee52-43b1-95d7-dc16dc2a30aa";i:4;s:84:"http://localhost:8000/clipper/limesurvey/666557/0da13f1d-660f-4a5c-b8d8-1347faa9bb87";i:5;s:84:"http://localhost:8000/clipper/limesurvey/666557/5f3f2752-e6b8-40e5-a319-c90388f92497";i:6;s:84:"http://localhost:8000/clipper/limesurvey/666557/7a0996c2-bc38-4ee7-b4d9-63804b86c150";i:7;s:84:"http://localhost:8000/clipper/limesurvey/666557/2ae4bedd-b84e-48ea-b8a6-95852cc67343";i:8;s:84:"http://localhost:8000/clipper/limesurvey/666557/4a551bc6-416c-41dc-8ac3-b66251030d28";i:9;s:84:"http://localhost:8000/clipper/limesurvey/666557/86fb9e43-ca79-4d40-bc4e-8e3067f8c67f";i:10;s:84:"http://localhost:8000/clipper/limesurvey/666557/7d3bd6f4-5b7d-488c-ac88-fdfc0d5f5717";i:11;s:84:"http://localhost:8000/clipper/limesurvey/666557/0b294f92-b483-4cc1-9601-3447ea89fe2e";i:12;s:84:"http://localhost:8000/clipper/limesurvey/666557/f453ee25-57fb-456d-a1fc-3bb889a949aa";}}');
    $manager->persist($fq1);

    $fq2 = new FirstQProject();
    $fq2->setBcProductId(47);
    $fq2->setFormDataRaw('O:8:"stdClass":10:{s:3:"loi";i:10;s:2:"ir";i:10;s:5:"title";s:13:"A survey name";s:4:"name";s:3:"FQ3";s:12:"patient_type";s:14:"A Patient type";s:16:"num_participants";i:35;s:6:"market";s:3:"USA";s:9:"specialty";s:10:"Cardiology";s:9:"timestamp";s:13:"1433963299113";s:5:"brand";a:4:{i:0;s:7:"Brand A";i:1;s:7:"Brand B";i:2;s:7:"Brand C";i:3;s:7:"Brand D";}}');
    $fq2->setSheetDataRaw('O:8:"stdClass":3:{s:11:"feasibility";b:1;s:11:"description";s:75:"Size of Universe Represented 6,361 - Percent of Universe Represented 37500%";s:6:"result";a:16:{s:2:"F3";s:5:"6,361";s:2:"F5";s:5:"8,866";s:2:"F7";s:2:"45";s:2:"F8";s:4:"2250";s:3:"F10";s:1:"0";s:3:"F12";s:3:"GBP";s:3:"F14";s:6:"232032";s:3:"F15";s:7:"116,016";s:3:"F16";s:6:"51,334";s:3:"F17";s:7:"167,350";s:3:"F20";s:1:"0";s:3:"F21";s:5:"$0.00";s:3:"F22";s:5:"$0.00";s:3:"F24";s:7:"167,350";s:3:"F26";s:6:"$41.00";s:3:"F27";s:3:"USD";}}');
    $fq2->setState("BIGCOMMERCE_COMPLETE");
    $fq2->setLimesurveyDataRaw('a:2:{s:3:"sid";i:666557;s:4:"urls";a:13:{i:0;s:84:"http://localhost:8000/clipper/limesurvey/666557/419266f0-d368-46d2-9c4b-3accd0e91e97";i:1;s:84:"http://localhost:8000/clipper/limesurvey/666557/80e2a623-0c75-41f0-8bf1-e28a62e597cb";i:2;s:84:"http://localhost:8000/clipper/limesurvey/666557/37dbfee7-fe7b-47c8-ae82-23cbfa36f6c5";i:3;s:84:"http://localhost:8000/clipper/limesurvey/666557/d9d6c675-ee52-43b1-95d7-dc16dc2a30aa";i:4;s:84:"http://localhost:8000/clipper/limesurvey/666557/0da13f1d-660f-4a5c-b8d8-1347faa9bb87";i:5;s:84:"http://localhost:8000/clipper/limesurvey/666557/5f3f2752-e6b8-40e5-a319-c90388f92497";i:6;s:84:"http://localhost:8000/clipper/limesurvey/666557/7a0996c2-bc38-4ee7-b4d9-63804b86c150";i:7;s:84:"http://localhost:8000/clipper/limesurvey/666557/2ae4bedd-b84e-48ea-b8a6-95852cc67343";i:8;s:84:"http://localhost:8000/clipper/limesurvey/666557/4a551bc6-416c-41dc-8ac3-b66251030d28";i:9;s:84:"http://localhost:8000/clipper/limesurvey/666557/86fb9e43-ca79-4d40-bc4e-8e3067f8c67f";i:10;s:84:"http://localhost:8000/clipper/limesurvey/666557/7d3bd6f4-5b7d-488c-ac88-fdfc0d5f5717";i:11;s:84:"http://localhost:8000/clipper/limesurvey/666557/0b294f92-b483-4cc1-9601-3447ea89fe2e";i:12;s:84:"http://localhost:8000/clipper/limesurvey/666557/f453ee25-57fb-456d-a1fc-3bb889a949aa";}}');
    $manager->persist($fq2);

    $fq3 = new FirstQProject();
    $fq3->setBcProductId(3);
    $fq3->setFormDataRaw('O:8:"stdClass":10:{s:3:"loi";i:10;s:2:"ir";i:10;s:5:"title";s:13:"A survey name";s:4:"name";s:3:"FQ3";s:12:"patient_type";s:14:"A Patient type";s:16:"num_participants";i:35;s:6:"market";s:3:"USA";s:9:"specialty";s:10:"Cardiology";s:9:"timestamp";s:13:"1433963299113";s:5:"brand";a:4:{i:0;s:7:"Brand A";i:1;s:7:"Brand B";i:2;s:7:"Brand C";i:3;s:7:"Brand D";}}');
    $fq3->setSheetDataRaw('O:8:"stdClass":3:{s:11:"feasibility";b:1;s:11:"description";s:75:"Size of Universe Represented 6,361 - Percent of Universe Represented 37500%";s:6:"result";a:16:{s:2:"F3";s:5:"6,361";s:2:"F5";s:5:"8,866";s:2:"F7";s:2:"45";s:2:"F8";s:4:"2250";s:3:"F10";s:1:"0";s:3:"F12";s:3:"GBP";s:3:"F14";s:6:"232032";s:3:"F15";s:7:"116,016";s:3:"F16";s:6:"51,334";s:3:"F17";s:7:"167,350";s:3:"F20";s:1:"0";s:3:"F21";s:5:"$0.00";s:3:"F22";s:5:"$0.00";s:3:"F24";s:7:"167,350";s:3:"F26";s:6:"$41.00";s:3:"F27";s:3:"USD";}}');
    $fq3->setState("LIMESURVEY_CREATED");
    $fq3->setLimesurveyDataRaw('a:2:{s:3:"sid";i:666557;s:4:"urls";a:13:{i:0;s:84:"http://localhost:8000/clipper/limesurvey/666557/419266f0-d368-46d2-9c4b-3accd0e91e97";i:1;s:84:"http://localhost:8000/clipper/limesurvey/666557/80e2a623-0c75-41f0-8bf1-e28a62e597cb";i:2;s:84:"http://localhost:8000/clipper/limesurvey/666557/37dbfee7-fe7b-47c8-ae82-23cbfa36f6c5";i:3;s:84:"http://localhost:8000/clipper/limesurvey/666557/d9d6c675-ee52-43b1-95d7-dc16dc2a30aa";i:4;s:84:"http://localhost:8000/clipper/limesurvey/666557/0da13f1d-660f-4a5c-b8d8-1347faa9bb87";i:5;s:84:"http://localhost:8000/clipper/limesurvey/666557/5f3f2752-e6b8-40e5-a319-c90388f92497";i:6;s:84:"http://localhost:8000/clipper/limesurvey/666557/7a0996c2-bc38-4ee7-b4d9-63804b86c150";i:7;s:84:"http://localhost:8000/clipper/limesurvey/666557/2ae4bedd-b84e-48ea-b8a6-95852cc67343";i:8;s:84:"http://localhost:8000/clipper/limesurvey/666557/4a551bc6-416c-41dc-8ac3-b66251030d28";i:9;s:84:"http://localhost:8000/clipper/limesurvey/666557/86fb9e43-ca79-4d40-bc4e-8e3067f8c67f";i:10;s:84:"http://localhost:8000/clipper/limesurvey/666557/7d3bd6f4-5b7d-488c-ac88-fdfc0d5f5717";i:11;s:84:"http://localhost:8000/clipper/limesurvey/666557/0b294f92-b483-4cc1-9601-3447ea89fe2e";i:12;s:84:"http://localhost:8000/clipper/limesurvey/666557/f453ee25-57fb-456d-a1fc-3bb889a949aa";}}');
    $manager->persist($fq3);

    $fq4 = new FirstQProject();
    $fq4->setBcProductId(4);
    $fq4->setFormDataRaw('O:8:"stdClass":10:{s:3:"loi";i:10;s:2:"ir";i:10;s:5:"title";s:13:"A survey name";s:4:"name";s:3:"FQ3";s:12:"patient_type";s:14:"A Patient type";s:16:"num_participants";i:35;s:6:"market";s:3:"USA";s:9:"specialty";s:10:"Cardiology";s:9:"timestamp";s:13:"1433963299113";s:5:"brand";a:4:{i:0;s:7:"Brand A";i:1;s:7:"Brand B";i:2;s:7:"Brand C";i:3;s:7:"Brand D";}}');
    $fq4->setSheetDataRaw('O:8:"stdClass":3:{s:11:"feasibility";b:1;s:11:"description";s:75:"Size of Universe Represented 6,361 - Percent of Universe Represented 37500%";s:6:"result";a:16:{s:2:"F3";s:5:"6,361";s:2:"F5";s:5:"8,866";s:2:"F7";s:2:"45";s:2:"F8";s:4:"2250";s:3:"F10";s:1:"0";s:3:"F12";s:3:"GBP";s:3:"F14";s:6:"232032";s:3:"F15";s:7:"116,016";s:3:"F16";s:6:"51,334";s:3:"F17";s:7:"167,350";s:3:"F20";s:1:"0";s:3:"F21";s:5:"$0.00";s:3:"F22";s:5:"$0.00";s:3:"F24";s:7:"167,350";s:3:"F26";s:6:"$41.00";s:3:"F27";s:3:"USD";}}');
    $fq4->setState("RPANEL_COMPLETE");
    $fq4->setLimesurveyDataRaw('a:2:{s:3:"sid";i:666557;s:4:"urls";a:13:{i:0;s:84:"http://localhost:8000/clipper/limesurvey/666557/419266f0-d368-46d2-9c4b-3accd0e91e97";i:1;s:84:"http://localhost:8000/clipper/limesurvey/666557/80e2a623-0c75-41f0-8bf1-e28a62e597cb";i:2;s:84:"http://localhost:8000/clipper/limesurvey/666557/37dbfee7-fe7b-47c8-ae82-23cbfa36f6c5";i:3;s:84:"http://localhost:8000/clipper/limesurvey/666557/d9d6c675-ee52-43b1-95d7-dc16dc2a30aa";i:4;s:84:"http://localhost:8000/clipper/limesurvey/666557/0da13f1d-660f-4a5c-b8d8-1347faa9bb87";i:5;s:84:"http://localhost:8000/clipper/limesurvey/666557/5f3f2752-e6b8-40e5-a319-c90388f92497";i:6;s:84:"http://localhost:8000/clipper/limesurvey/666557/7a0996c2-bc38-4ee7-b4d9-63804b86c150";i:7;s:84:"http://localhost:8000/clipper/limesurvey/666557/2ae4bedd-b84e-48ea-b8a6-95852cc67343";i:8;s:84:"http://localhost:8000/clipper/limesurvey/666557/4a551bc6-416c-41dc-8ac3-b66251030d28";i:9;s:84:"http://localhost:8000/clipper/limesurvey/666557/86fb9e43-ca79-4d40-bc4e-8e3067f8c67f";i:10;s:84:"http://localhost:8000/clipper/limesurvey/666557/7d3bd6f4-5b7d-488c-ac88-fdfc0d5f5717";i:11;s:84:"http://localhost:8000/clipper/limesurvey/666557/0b294f92-b483-4cc1-9601-3447ea89fe2e";i:12;s:84:"http://localhost:8000/clipper/limesurvey/666557/f453ee25-57fb-456d-a1fc-3bb889a949aa";}}');
    $manager->persist($fq4);

    $fq5 = new FirstQProject();
    $fq5->setBcProductId(5);
    $fq5->setFormDataRaw('O:8:"stdClass":10:{s:3:"loi";i:10;s:2:"ir";i:10;s:5:"title";s:13:"A survey name";s:4:"name";s:3:"FQ3";s:12:"patient_type";s:14:"A Patient type";s:16:"num_participants";i:35;s:6:"market";s:3:"USA";s:9:"specialty";s:10:"Cardiology";s:9:"timestamp";s:13:"1433963299113";s:5:"brand";a:4:{i:0;s:7:"Brand A";i:1;s:7:"Brand B";i:2;s:7:"Brand C";i:3;s:7:"Brand D";}}');
    $fq5->setSheetDataRaw('O:8:"stdClass":3:{s:11:"feasibility";b:1;s:11:"description";s:75:"Size of Universe Represented 6,361 - Percent of Universe Represented 37500%";s:6:"result";a:16:{s:2:"F3";s:5:"6,361";s:2:"F5";s:5:"8,866";s:2:"F7";s:2:"45";s:2:"F8";s:4:"2250";s:3:"F10";s:1:"0";s:3:"F12";s:3:"GBP";s:3:"F14";s:6:"232032";s:3:"F15";s:7:"116,016";s:3:"F16";s:6:"51,334";s:3:"F17";s:7:"167,350";s:3:"F20";s:1:"0";s:3:"F21";s:5:"$0.00";s:3:"F22";s:5:"$0.00";s:3:"F24";s:7:"167,350";s:3:"F26";s:6:"$41.00";s:3:"F27";s:3:"USD";}}');
    $fq5->setState("LIMESURVEY_COMPLETE");
    $fq5->setLimesurveyDataRaw('a:2:{s:3:"sid";i:666557;s:4:"urls";a:13:{i:0;s:84:"http://localhost:8000/clipper/limesurvey/666557/419266f0-d368-46d2-9c4b-3accd0e91e97";i:1;s:84:"http://localhost:8000/clipper/limesurvey/666557/80e2a623-0c75-41f0-8bf1-e28a62e597cb";i:2;s:84:"http://localhost:8000/clipper/limesurvey/666557/37dbfee7-fe7b-47c8-ae82-23cbfa36f6c5";i:3;s:84:"http://localhost:8000/clipper/limesurvey/666557/d9d6c675-ee52-43b1-95d7-dc16dc2a30aa";i:4;s:84:"http://localhost:8000/clipper/limesurvey/666557/0da13f1d-660f-4a5c-b8d8-1347faa9bb87";i:5;s:84:"http://localhost:8000/clipper/limesurvey/666557/5f3f2752-e6b8-40e5-a319-c90388f92497";i:6;s:84:"http://localhost:8000/clipper/limesurvey/666557/7a0996c2-bc38-4ee7-b4d9-63804b86c150";i:7;s:84:"http://localhost:8000/clipper/limesurvey/666557/2ae4bedd-b84e-48ea-b8a6-95852cc67343";i:8;s:84:"http://localhost:8000/clipper/limesurvey/666557/4a551bc6-416c-41dc-8ac3-b66251030d28";i:9;s:84:"http://localhost:8000/clipper/limesurvey/666557/86fb9e43-ca79-4d40-bc4e-8e3067f8c67f";i:10;s:84:"http://localhost:8000/clipper/limesurvey/666557/7d3bd6f4-5b7d-488c-ac88-fdfc0d5f5717";i:11;s:84:"http://localhost:8000/clipper/limesurvey/666557/0b294f92-b483-4cc1-9601-3447ea89fe2e";i:12;s:84:"http://localhost:8000/clipper/limesurvey/666557/f453ee25-57fb-456d-a1fc-3bb889a949aa";}}');
    $manager->persist($fq5);

    $fq6 = new FirstQProject();
    $fq6->setBcProductId(6);
    $fq6->setFormDataRaw('O:8:"stdClass":10:{s:3:"loi";i:10;s:2:"ir";i:10;s:5:"title";s:13:"A survey name";s:4:"name";s:3:"FQ3";s:12:"patient_type";s:14:"A Patient type";s:16:"num_participants";i:35;s:6:"market";s:3:"USA";s:9:"specialty";s:10:"Cardiology";s:9:"timestamp";s:13:"1433963299113";s:5:"brand";a:4:{i:0;s:7:"Brand A";i:1;s:7:"Brand B";i:2;s:7:"Brand C";i:3;s:7:"Brand D";}}');
    $fq6->setSheetDataRaw('O:8:"stdClass":3:{s:11:"feasibility";b:1;s:11:"description";s:75:"Size of Universe Represented 6,361 - Percent of Universe Represented 37500%";s:6:"result";a:16:{s:2:"F3";s:5:"6,361";s:2:"F5";s:5:"8,866";s:2:"F7";s:2:"45";s:2:"F8";s:4:"2250";s:3:"F10";s:1:"0";s:3:"F12";s:3:"GBP";s:3:"F14";s:6:"232032";s:3:"F15";s:7:"116,016";s:3:"F16";s:6:"51,334";s:3:"F17";s:7:"167,350";s:3:"F20";s:1:"0";s:3:"F21";s:5:"$0.00";s:3:"F22";s:5:"$0.00";s:3:"F24";s:7:"167,350";s:3:"F26";s:6:"$41.00";s:3:"F27";s:3:"USD";}}');
    $fq6->setState("EMAIL_SENT");
    $fq6->setLimesurveyDataRaw('a:2:{s:3:"sid";i:666557;s:4:"urls";a:13:{i:0;s:84:"http://localhost:8000/clipper/limesurvey/666557/419266f0-d368-46d2-9c4b-3accd0e91e97";i:1;s:84:"http://localhost:8000/clipper/limesurvey/666557/80e2a623-0c75-41f0-8bf1-e28a62e597cb";i:2;s:84:"http://localhost:8000/clipper/limesurvey/666557/37dbfee7-fe7b-47c8-ae82-23cbfa36f6c5";i:3;s:84:"http://localhost:8000/clipper/limesurvey/666557/d9d6c675-ee52-43b1-95d7-dc16dc2a30aa";i:4;s:84:"http://localhost:8000/clipper/limesurvey/666557/0da13f1d-660f-4a5c-b8d8-1347faa9bb87";i:5;s:84:"http://localhost:8000/clipper/limesurvey/666557/5f3f2752-e6b8-40e5-a319-c90388f92497";i:6;s:84:"http://localhost:8000/clipper/limesurvey/666557/7a0996c2-bc38-4ee7-b4d9-63804b86c150";i:7;s:84:"http://localhost:8000/clipper/limesurvey/666557/2ae4bedd-b84e-48ea-b8a6-95852cc67343";i:8;s:84:"http://localhost:8000/clipper/limesurvey/666557/4a551bc6-416c-41dc-8ac3-b66251030d28";i:9;s:84:"http://localhost:8000/clipper/limesurvey/666557/86fb9e43-ca79-4d40-bc4e-8e3067f8c67f";i:10;s:84:"http://localhost:8000/clipper/limesurvey/666557/7d3bd6f4-5b7d-488c-ac88-fdfc0d5f5717";i:11;s:84:"http://localhost:8000/clipper/limesurvey/666557/0b294f92-b483-4cc1-9601-3447ea89fe2e";i:12;s:84:"http://localhost:8000/clipper/limesurvey/666557/f453ee25-57fb-456d-a1fc-3bb889a949aa";}}');
    $manager->persist($fq6);

    $manager->flush();
  }

}
