<?php

namespace PSL\ClipperBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PSL\ClipperBundle\Entity\FirstQGroup;
use PSL\ClipperBundle\Entity\FirstQProject;
use PSL\ClipperBundle\Entity\LimeSurveyResponse;

class LoadLimeSurveyResponse extends AbstractFixture implements OrderedFixtureInterface
{
  /**
  * {@inheritDoc}
  */
  public function load(ObjectManager $manager)
  {
    $lsr = new LimeSurveyResponse();
    $lsr->setLsToken('token-1');
    $lsr->setMemberId(1);
    $lsr->setResponseRaw('{"id":"145","submitdate":"2015-09-15 05:57:22","lastpage":"15","startlanguage":"en","token":"8axzyu3itpcgzkc","startdate":"2015-09-15 05:57:22","datestamp":"2015-09-15 05:57:22","G001Q001[SQ001]":"I have never used this drug and would not consider it","G001Q001[SQ002]":"I have used this drug previously but would no longer consider it","G001Q001[SQ003]":"I have used this drug previously and would consider using it again","G001Q001[SQ004]":"I have used this drug previously but would no longer consider it","G001Q001[SQ005]":"I currently use this drug","G001Q001[SQ006]":"I am not aware of this drug","G001Q002[SQ001]":"","G001Q002[SQ002]":"","G001Q002[SQ003]":"","G001Q002[SQ004]":"","G001Q002[SQ005]":"yes","G001Q002[SQ006]":"","G001Q003[SQ001]":"yes","G001Q003[SQ002]":"yes","G001Q003[SQ003]":"yes","G001Q003[SQ004]":"yes","G001Q003[SQ005]":"yes","G001Q003[SQ006]":"","G001Q004":"108","G001Q005":"240","G001Q006":"","G002Q001[SQ001_SQ001]":"","G002Q001[SQ002_SQ001]":"","G002Q001[SQ003_SQ001]":"","G002Q001[SQ004_SQ001]":"","G002Q001[SQ005_SQ001]":"100","G002Q001[SQ006_SQ001]":"","G002Q002":"","G003Q001[SQ001]":"4","G003Q001[SQ002]":"6","G003Q001[SQ003]":"7","G003Q001[SQ004]":"9","G003Q001[SQ005]":"10","G003Q001[SQ006]":"","G003Q002":"","G004Q001":"Too expensive","G004Q002":"","G005Q001":"No marketing","G005Q002":"","G006Q001":"Too many side effects","G006Q002":"","G007Q001":"I don\'t trust double D","G007Q002":"","G008Q001":"Simply works","G008Q002":"","G009Q001":"","G009Q002":"","G0010Q001[SQ001]":"No","G0010Q001[SQ002]":"No","G0010Q001[SQ003]":"No","G0010Q001[SQ004]":"Yes","G0010Q001[SQ005]":"No","G0010Q001[SQ006]":"No","G0010Q001[SQ007]":"No","G0010Q001[SQ008]":"No","G0010Q002":"","G0011Q001[SQ001]":"Yes","G0011Q001[SQ002]":"No","G0011Q001[SQ003]":"Yes","G0011Q001[SQ004]":"Yes","G0011Q001[SQ005]":"Yes","G0011Q001[SQ006]":"No","G0011Q001[SQ007]":"Yes","G0011Q001[SQ008]":"Yes","G0011Q002":"","G0012Q001[SQ001]":"No","G0012Q001[SQ002]":"Yes","G0012Q001[SQ003]":"No","G0012Q001[SQ004]":"No","G0012Q001[SQ005]":"No","G0012Q001[SQ006]":"No","G0012Q001[SQ007]":"No","G0012Q001[SQ008]":"No","G0012Q002":"","G0013Q001[SQ001]":"Yes","G0013Q001[SQ002]":"No","G0013Q001[SQ003]":"No","G0013Q001[SQ004]":"No","G0013Q001[SQ005]":"No","G0013Q001[SQ006]":"Yes","G0013Q001[SQ007]":"No","G0013Q001[SQ008]":"No","G0013Q002":"","G0014Q001[SQ001]":"Yes","G0014Q001[SQ002]":"No","G0014Q001[SQ003]":"No","G0014Q001[SQ004]":"No","G0014Q001[SQ005]":"Yes","G0014Q001[SQ006]":"Yes","G0014Q001[SQ007]":"No","G0014Q001[SQ008]":"No","G0014Q002":"","G0015Q001[SQ001]":"","G0015Q001[SQ002]":"","G0015Q001[SQ003]":"","G0015Q001[SQ004]":"","G0015Q001[SQ005]":"","G0015Q001[SQ006]":"","G0015Q001[SQ007]":"","G0015Q001[SQ008]":"","G0015Q002":""}');
    $lsr->setFirstqgroup($this->getReference('firstqgroup-1'));
    $lsr->setFirstqproject($this->getReference('firstqproject-1'));
    $manager->persist($lsr);
    
    $lsr = new LimeSurveyResponse();
    $lsr->setLsToken('token-2');
    $lsr->setMemberId(1);
    $lsr->setResponseRaw('{"id":"145","submitdate":"2015-09-15 05:57:22","lastpage":"15","startlanguage":"en","token":"8axzyu3itpcgzkc","startdate":"2015-09-15 05:57:22","datestamp":"2015-09-15 05:57:22","G001Q001[SQ001]":"I have never used this drug and would not consider it","G001Q001[SQ002]":"I have used this drug previously but would no longer consider it","G001Q001[SQ003]":"I have used this drug previously and would consider using it again","G001Q001[SQ004]":"I have used this drug previously but would no longer consider it","G001Q001[SQ005]":"I currently use this drug","G001Q001[SQ006]":"I am not aware of this drug","G001Q002[SQ001]":"","G001Q002[SQ002]":"","G001Q002[SQ003]":"","G001Q002[SQ004]":"","G001Q002[SQ005]":"yes","G001Q002[SQ006]":"","G001Q003[SQ001]":"yes","G001Q003[SQ002]":"yes","G001Q003[SQ003]":"yes","G001Q003[SQ004]":"yes","G001Q003[SQ005]":"yes","G001Q003[SQ006]":"","G001Q004":"108","G001Q005":"240","G001Q006":"","G002Q001[SQ001_SQ001]":"","G002Q001[SQ002_SQ001]":"","G002Q001[SQ003_SQ001]":"","G002Q001[SQ004_SQ001]":"","G002Q001[SQ005_SQ001]":"100","G002Q001[SQ006_SQ001]":"","G002Q002":"","G003Q001[SQ001]":"4","G003Q001[SQ002]":"6","G003Q001[SQ003]":"7","G003Q001[SQ004]":"9","G003Q001[SQ005]":"10","G003Q001[SQ006]":"","G003Q002":"","G004Q001":"Too expensive","G004Q002":"","G005Q001":"No marketing","G005Q002":"","G006Q001":"Too many side effects","G006Q002":"","G007Q001":"I don\'t trust double D","G007Q002":"","G008Q001":"Simply works","G008Q002":"","G009Q001":"","G009Q002":"","G0010Q001[SQ001]":"No","G0010Q001[SQ002]":"No","G0010Q001[SQ003]":"No","G0010Q001[SQ004]":"Yes","G0010Q001[SQ005]":"No","G0010Q001[SQ006]":"No","G0010Q001[SQ007]":"No","G0010Q001[SQ008]":"No","G0010Q002":"","G0011Q001[SQ001]":"Yes","G0011Q001[SQ002]":"No","G0011Q001[SQ003]":"Yes","G0011Q001[SQ004]":"Yes","G0011Q001[SQ005]":"Yes","G0011Q001[SQ006]":"No","G0011Q001[SQ007]":"Yes","G0011Q001[SQ008]":"Yes","G0011Q002":"","G0012Q001[SQ001]":"No","G0012Q001[SQ002]":"Yes","G0012Q001[SQ003]":"No","G0012Q001[SQ004]":"No","G0012Q001[SQ005]":"No","G0012Q001[SQ006]":"No","G0012Q001[SQ007]":"No","G0012Q001[SQ008]":"No","G0012Q002":"","G0013Q001[SQ001]":"Yes","G0013Q001[SQ002]":"No","G0013Q001[SQ003]":"No","G0013Q001[SQ004]":"No","G0013Q001[SQ005]":"No","G0013Q001[SQ006]":"Yes","G0013Q001[SQ007]":"No","G0013Q001[SQ008]":"No","G0013Q002":"","G0014Q001[SQ001]":"Yes","G0014Q001[SQ002]":"No","G0014Q001[SQ003]":"No","G0014Q001[SQ004]":"No","G0014Q001[SQ005]":"Yes","G0014Q001[SQ006]":"Yes","G0014Q001[SQ007]":"No","G0014Q001[SQ008]":"No","G0014Q002":"","G0015Q001[SQ001]":"","G0015Q001[SQ002]":"","G0015Q001[SQ003]":"","G0015Q001[SQ004]":"","G0015Q001[SQ005]":"","G0015Q001[SQ006]":"","G0015Q001[SQ007]":"","G0015Q001[SQ008]":"","G0015Q002":""}');
    $lsr->setFirstqgroup($this->getReference('firstqgroup-1'));
    $lsr->setFirstqproject($this->getReference('firstqproject-1'));
    $manager->persist($lsr);
    
    $lsr = new LimeSurveyResponse();
    $lsr->setLsToken('token-3');
    $lsr->setMemberId(1);
    $lsr->setResponseRaw('{"id":"155","submitdate":"2015-09-15 06:03:01","lastpage":"15","startlanguage":"en","token":"cypuw9x7tvm5d4k","startdate":"2015-09-15 06:03:01","datestamp":"2015-09-15 06:03:01","G001Q001[SQ001]":"I currently use this drug","G001Q001[SQ002]":"I currently use this drug","G001Q001[SQ003]":"I have used this drug previously but would no longer consider it","G001Q001[SQ004]":"I am not aware of this drug","G001Q001[SQ005]":"I am not aware of this drug","G001Q001[SQ006]":"I currently use this drug","G001Q002[SQ001]":"yes","G001Q002[SQ002]":"yes","G001Q002[SQ003]":"","G001Q002[SQ004]":"","G001Q002[SQ005]":"","G001Q002[SQ006]":"yes","G001Q003[SQ001]":"yes","G001Q003[SQ002]":"yes","G001Q003[SQ003]":"yes","G001Q003[SQ004]":"","G001Q003[SQ005]":"","G001Q003[SQ006]":"yes","G001Q004":"108","G001Q005":"240","G001Q006":"","G002Q001[SQ001_SQ001]":"80","G002Q001[SQ002_SQ001]":"15","G002Q001[SQ003_SQ001]":"","G002Q001[SQ004_SQ001]":"","G002Q001[SQ005_SQ001]":"","G002Q001[SQ006_SQ001]":"5","G002Q002":"","G003Q001[SQ001]":"10","G003Q001[SQ002]":"7","G003Q001[SQ003]":"9","G003Q001[SQ004]":"","G003Q001[SQ005]":"","G003Q001[SQ006]":"1","G003Q002":"","G004Q001":"It\'s AA","G004Q002":"","G005Q001":"Not sure about B","G005Q002":"","G006Q001":"Im ok in C","G006Q002":"","G007Q001":"","G007Q002":"","G008Q001":"","G008Q002":"","G009Q001":"I can\'t manage for an F","G009Q002":"","G0010Q001[SQ001]":"Yes","G0010Q001[SQ002]":"No","G0010Q001[SQ003]":"Yes","G0010Q001[SQ004]":"No","G0010Q001[SQ005]":"No","G0010Q001[SQ006]":"Yes","G0010Q001[SQ007]":"No","G0010Q001[SQ008]":"No","G0010Q002":"","G0011Q001[SQ001]":"Yes","G0011Q001[SQ002]":"No","G0011Q001[SQ003]":"No","G0011Q001[SQ004]":"No","G0011Q001[SQ005]":"Yes","G0011Q001[SQ006]":"Yes","G0011Q001[SQ007]":"No","G0011Q001[SQ008]":"No","G0011Q002":"","G0012Q001[SQ001]":"No","G0012Q001[SQ002]":"No","G0012Q001[SQ003]":"Yes","G0012Q001[SQ004]":"Yes","G0012Q001[SQ005]":"Yes","G0012Q001[SQ006]":"No","G0012Q001[SQ007]":"No","G0012Q001[SQ008]":"Yes","G0012Q002":"","G0013Q001[SQ001]":"","G0013Q001[SQ002]":"","G0013Q001[SQ003]":"","G0013Q001[SQ004]":"","G0013Q001[SQ005]":"","G0013Q001[SQ006]":"","G0013Q001[SQ007]":"","G0013Q001[SQ008]":"","G0013Q002":"","G0014Q001[SQ001]":"","G0014Q001[SQ002]":"","G0014Q001[SQ003]":"","G0014Q001[SQ004]":"","G0014Q001[SQ005]":"","G0014Q001[SQ006]":"","G0014Q001[SQ007]":"","G0014Q001[SQ008]":"","G0014Q002":"","G0015Q001[SQ001]":"No","G0015Q001[SQ002]":"Yes","G0015Q001[SQ003]":"Yes","G0015Q001[SQ004]":"Yes","G0015Q001[SQ005]":"Yes","G0015Q001[SQ006]":"Yes","G0015Q001[SQ007]":"Yes","G0015Q001[SQ008]":"Yes","G0015Q002":""}');
    $lsr->setFirstqgroup($this->getReference('firstqgroup-1'));
    $lsr->setFirstqproject($this->getReference('firstqproject-1'));
    $manager->persist($lsr);
    
    $lsr = new LimeSurveyResponse();
    $lsr->setLsToken('token-4');
    $lsr->setMemberId(1);
    $lsr->setResponseRaw('{"id":"165","submitdate":"2015-09-15 06:36:55","lastpage":"15","startlanguage":"en","token":"fhgjs3fhs3dfgh","startdate":"2015-09-15 06:36:55","datestamp":"2015-09-15 06:36:55","G001Q001[SQ001]":"I currently use this drug","G001Q001[SQ002]":"I have used this drug previously and would consider using it again","G001Q001[SQ003]":"I have used this drug previously but would no longer consider it","G001Q001[SQ004]":"I have never used this drug but would consider it","G001Q001[SQ005]":"I have never used this drug and would not consider it","G001Q001[SQ006]":"I am not aware of this drug","G001Q002[SQ001]":"yes","G001Q002[SQ002]":"","G001Q002[SQ003]":"","G001Q002[SQ004]":"","G001Q002[SQ005]":"","G001Q002[SQ006]":"","G001Q003[SQ001]":"yes","G001Q003[SQ002]":"yes","G001Q003[SQ003]":"yes","G001Q003[SQ004]":"yes","G001Q003[SQ005]":"yes","G001Q003[SQ006]":"","G001Q004":"108","G001Q005":"240","G001Q006":"","G002Q001[SQ001_SQ001]":"100","G002Q001[SQ002_SQ001]":"","G002Q001[SQ003_SQ001]":"","G002Q001[SQ004_SQ001]":"","G002Q001[SQ005_SQ001]":"","G002Q001[SQ006_SQ001]":"","G002Q002":"","G003Q001[SQ001]":"10","G003Q001[SQ002]":"4","G003Q001[SQ003]":"3","G003Q001[SQ004]":"2","G003Q001[SQ005]":"1","G003Q001[SQ006]":"","G003Q002":"","G004Q001":"It works!","G004Q002":"","G005Q001":"I don\'t know if frog is reasonable","G005Q002":"","G006Q001":"Maxence Cyrin","G006Q002":"","G007Q001":"https:\\/\\/youtu.be\\/4NZdggNUvq0","G007Q002":"","G008Q001":"It\'s kind of a funny story","G008Q002":"","G009Q001":"","G009Q002":"","G0010Q001[SQ001]":"Yes","G0010Q001[SQ002]":"No","G0010Q001[SQ003]":"No","G0010Q001[SQ004]":"No","G0010Q001[SQ005]":"No","G0010Q001[SQ006]":"Yes","G0010Q001[SQ007]":"No","G0010Q001[SQ008]":"No","G0010Q002":"","G0011Q001[SQ001]":"No","G0011Q001[SQ002]":"Yes","G0011Q001[SQ003]":"Yes","G0011Q001[SQ004]":"Yes","G0011Q001[SQ005]":"Yes","G0011Q001[SQ006]":"No","G0011Q001[SQ007]":"Yes","G0011Q001[SQ008]":"Yes","G0011Q002":"","G0012Q001[SQ001]":"No","G0012Q001[SQ002]":"Yes","G0012Q001[SQ003]":"Yes","G0012Q001[SQ004]":"Yes","G0012Q001[SQ005]":"Yes","G0012Q001[SQ006]":"Yes","G0012Q001[SQ007]":"Yes","G0012Q001[SQ008]":"Yes","G0012Q002":"","G0013Q001[SQ001]":"No","G0013Q001[SQ002]":"Yes","G0013Q001[SQ003]":"Yes","G0013Q001[SQ004]":"Yes","G0013Q001[SQ005]":"Yes","G0013Q001[SQ006]":"Yes","G0013Q001[SQ007]":"Yes","G0013Q001[SQ008]":"Yes","G0013Q002":"","G0014Q001[SQ001]":"No","G0014Q001[SQ002]":"Yes","G0014Q001[SQ003]":"Yes","G0014Q001[SQ004]":"Yes","G0014Q001[SQ005]":"Yes","G0014Q001[SQ006]":"Yes","G0014Q001[SQ007]":"Yes","G0014Q001[SQ008]":"Yes","G0014Q002":"","G0015Q001[SQ001]":"","G0015Q001[SQ002]":"","G0015Q001[SQ003]":"","G0015Q001[SQ004]":"","G0015Q001[SQ005]":"","G0015Q001[SQ006]":"","G0015Q001[SQ007]":"","G0015Q001[SQ008]":"","G0015Q002":""}');
    $lsr->setFirstqgroup($this->getReference('firstqgroup-1'));
    $lsr->setFirstqproject($this->getReference('firstqproject-1'));    
    $manager->persist($lsr);

    $manager->flush();
  }
  
  /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 3; // the order in which fixtures will be loaded
    }
}