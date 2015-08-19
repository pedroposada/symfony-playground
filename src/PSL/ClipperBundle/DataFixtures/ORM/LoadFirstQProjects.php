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
    $fq1->setSheetDataRaw('{"market":"USA","specialty":"Oncology","feasibility":true,"participants_sample":"2500","price":"114,330","result":{"F3":"6,361","F5":"10,537","F7":"50","F8":"2500","F10":"0","F12":"GBP","F14":"114584","F15":"57,292","F16":"57,038","F17":"114,330","F20":"0","F21":"$0.00","F22":"$0.00","F24":"114,330","F26":"$18.00","F27":"USD"}}');
    $fq1->setState("LIMESURVEY_PENDING");
    $fq1->setLimesurveyDataRaw('{"participants":[{"sent":"N","remindersent":"N","remindercount":0,"completed":"N","usesleft":1,"email":"fq0@pslgroup.com","lastname":"fq0","firstname":"fq0","token":"be25q7undhkmmaj","tid":"5","participant_id":null,"emailstatus":null,"language":null,"blacklisted":null,"validfrom":null,"validuntil":null,"mpid":null},{"sent":"N","remindersent":"N","remindercount":0,"completed":"N","usesleft":1,"email":"fq1@pslgroup.com","lastname":"fq1","firstname":"fq1","token":"cue7xuka49tum3d","tid":"15","participant_id":null,"emailstatus":null,"language":null,"blacklisted":null,"validfrom":null,"validuntil":null,"mpid":null}],"sid":364651,"urls":["http:\/\/localhost:8000\/clipper\/limesurvey\/364651\/be25q7undhkmmaj\/en","http:\/\/localhost:8000\/clipper\/limesurvey\/364651\/cue7xuka49tum3d\/en"]}');
    $manager->persist($fq1);

    $manager->flush();
  }

}
