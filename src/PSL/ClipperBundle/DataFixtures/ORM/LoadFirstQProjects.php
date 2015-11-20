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
    public function getOrder()
    {
        return 2;
    }

    /**
    * {@inheritDoc}
    */
    public function load(ObjectManager $manager)
    {
        // new version NPS+ (FirstView NOV 2015)
        $fqp = new FirstQProject();
        $fqp->setSheetDataRaw('{"market":"USA","specialty":"Oncology","feasibility":true,"participants_sample":"2500","price":"114,330","num_participants":"100","result":{"F3":"6,361","F5":"10,537","F7":"50","F8":"2500","F10":"0","F12":"GBP","F14":"114584","F15":"57,292","F16":"57,038","F17":"114,330","F20":"0","F21":"$0.00","F22":"$0.00","F24":"114,330","F26":"$18.00","F27":"USD"}}');
        $fqp->setLimesurveyDataRaw('{"participants":[{"sent":"N","remindersent":"N","remindercount":0,"completed":"N","usesleft":1,"email":"fq0@pslgroup.com","lastname":"fq0","firstname":"fq0","token":"d2y300jGYrVGyUy","tid":"5","participant_id":null,"emailstatus":null,"language":null,"blacklisted":null,"validfrom":null,"validuntil":null,"mpid":null},{"sent":"N","remindersent":"N","remindercount":0,"completed":"N","usesleft":1,"email":"fq1@pslgroup.com","lastname":"fq1","firstname":"fq1","token":"d2y300jGYrVGyUy","tid":"15","participant_id":null,"emailstatus":null,"language":null,"blacklisted":null,"validfrom":null,"validuntil":null,"mpid":null}],"sid":779321,"urls":["http:\/\/localhost:8000\/clipper\/limesurvey\/779321\/d2y300jGYrVGyUy\/en","http:\/\/localhost:8000\/clipper\/limesurvey\/779321\/d2y300jGYrVGyUy\/en"]}');
        $fqp->setState("LIMESURVEY_CREATED");
        $fqp->setFirstqgroup($this->getReference('firstqgroup-1'));
        $this->setReference('firstqproject-1', $fqp);
        $manager->persist($fqp);
        
        $fqp = new FirstQProject();
        $fqp->setSheetDataRaw('{"market":"USA","specialty":"Oncology","feasibility":true,"participants_sample":"2500","price":"114,330","num_participants":"100","result":{"F3":"25,541","F5":"34,528","F7":"50","F8":"2500","F10":"0","F12":"GBP","F14":"114584","F15":"57,292","F16":"57,038","F17":"114,330","F20":"0","F21":"$0.00","F22":"$0.00","F24":"114,330","F26":"$18.00","F27":"USD"}}');
        $fqp->setState("LIMESURVEY_PENDING");
        $fqp->setFirstqgroup($this->getReference('firstqgroup-1'));
        $manager->persist($fqp);
          
        // previous version NPS+
        $fqp = new FirstQProject();
        $fqp->setSheetDataRaw('{"market":"USA","specialty":"Cardiology","feasibility":true,"participants_sample":"2500","price":"114,330","num_participants":"100","result":{"F3":"25,541","F5":"34,528","F7":"50","F8":"2500","F10":"0","F12":"GBP","F14":"114584","F15":"57,292","F16":"57,038","F17":"114,330","F20":"0","F21":"$0.00","F22":"$0.00","F24":"114,330","F26":"$18.00","F27":"USD"}}');
        $fqp->setLimesurveyDataRaw('{"participants":[{"sent":"N","remindersent":"N","remindercount":0,"completed":"N","usesleft":1,"email":"fq0@pslgroup.com","lastname":"fq0","firstname":"fq0","token":"bw8n2jfkjghiudn","tid":"5","participant_id":null,"emailstatus":null,"language":null,"blacklisted":null,"validfrom":null,"validuntil":null,"mpid":null},{"sent":"N","remindersent":"N","remindercount":0,"completed":"N","usesleft":1,"email":"fq1@pslgroup.com","lastname":"fq1","firstname":"fq1","token":"bw8n2jfkjghiudn","tid":"15","participant_id":null,"emailstatus":null,"language":null,"blacklisted":null,"validfrom":null,"validuntil":null,"mpid":null}],"sid":466611,"urls":["http:\/\/localhost:8000\/clipper\/limesurvey\/466611\/bw8n2jfkjghiudn\/en","http:\/\/localhost:8000\/clipper\/limesurvey\/466611\/bw8n2jfkjghiudn\/en"]}');
        $fqp->setState("LIMESURVEY_CREATED");
        $fqp->setFirstqgroup($this->getReference('firstqgroup-2'));
        $this->setReference('firstqproject-2', $fqp);
        $manager->persist($fqp);

        /**
         * Added for test unit
         */
        $fqp = new FirstQProject();
        $fqp->setSheetDataRaw('{"market":"USA","specialty":"Oncology","feasibility":true,"participants_sample":"2500","price":"114,330","num_participants":"100","result":{"F3":"6,361","F5":"10,537","F7":"50","F8":"2500","F10":"0","F12":"GBP","F14":"114584","F15":"57,292","F16":"57,038","F17":"114,330","F20":"0","F21":"$0.00","F22":"$0.00","F24":"114,330","F26":"$18.00","F27":"USD"}}');
        $fqp->setState("LIMESURVEY_PENDING");
        $fqp->setFirstqgroup($this->getReference('firstqgroup-test-003'));
        $manager->persist($fqp);

        $fqp = new FirstQProject();
        $fqp->setSheetDataRaw('{"market":"USA","specialty":"Cardiology","feasibility":true,"participants_sample":"2500","price":"114,330","num_participants":"100","result":{"F3":"25,541","F5":"34,528","F7":"50","F8":"2500","F10":"0","F12":"GBP","F14":"114584","F15":"57,292","F16":"57,038","F17":"114,330","F20":"0","F21":"$0.00","F22":"$0.00","F24":"114,330","F26":"$18.00","F27":"USD"}}');
        $fqp->setState("LIMESURVEY_PENDING");
        $fqp->setFirstqgroup($this->getReference('firstqgroup-test-002'));
        $manager->persist($fqp);

        $fqp = new FirstQProject();
        $fqp->setSheetDataRaw('{"market":"USA","specialty":"Cardiology","feasibility":true,"participants_sample":"2500","price":"114,330","num_participants":"100","result":{"F3":"25,541","F5":"34,528","F7":"50","F8":"2500","F10":"0","F12":"GBP","F14":"114584","F15":"57,292","F16":"57,038","F17":"114,330","F20":"0","F21":"$0.00","F22":"$0.00","F24":"114,330","F26":"$18.00","F27":"USD"}}');
        $fqp->setLimesurveyDataRaw('{"participants":[{"sent":"N","remindersent":"N","remindercount":0,"completed":"N","usesleft":1,"email":"fq0@pslgroup.com","lastname":"fq0","firstname":"fq0","token":"rp3dkms5q5hwy7m","tid":"5","participant_id":null,"emailstatus":null,"language":null,"blacklisted":null,"validfrom":null,"validuntil":null,"mpid":null},{"sent":"N","remindersent":"N","remindercount":0,"completed":"N","usesleft":1,"email":"fq1@pslgroup.com","lastname":"fq1","firstname":"fq1","token":"bw8n2jfkjghiudn","tid":"15","participant_id":null,"emailstatus":null,"language":null,"blacklisted":null,"validfrom":null,"validuntil":null,"mpid":null}],"sid":466611,"urls":["http:\/\/localhost:8000\/clipper\/limesurvey\/466611\/rp3dkms5q5hwy7m\/en","http:\/\/localhost:8000\/clipper\/limesurvey\/466611\/bw8n2jfkjghiudn\/en"]}');
        $fqp->setState("LIMESURVEY_CREATED");
        $fqp->setFirstqgroup($this->getReference('firstqgroup-test-001'));
        $this->setReference('firstqproject-test-001', $fqp);
        $manager->persist($fqp);

        $manager->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 2; // the order in which fixtures will be loaded
    }

