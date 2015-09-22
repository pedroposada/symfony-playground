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
        $group = new FirstQGroup();
        $group->setFormDataRaw('{"loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","timezone_client":"Europe\/London"}');
        $group->setState('ORDER_PENDING');
        $group->setOrderId('test-order-pending');
        $group->setUserId('test-user-id-1');
        $manager->persist($group);
        $this->addReference('firstqgroup', $group);

        $group = new FirstQGroup();
        $group->setFormDataRaw('{"loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","timezone_client":"Europe\/London"}');
        $group->setState('ORDER_COMPLETE');
        $group->setOrderId('test-order-complete');
        $group->setUserId('test-user-id-2');
        $manager->persist($group);
        $this->setReference('firstqgroup', $group);

        $group = new FirstQGroup();
        $group->setFormDataRaw('{"loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","timezone_client":"Europe\/London"}');
        $group->setState('ORDER_INVOICE');
        $group->setOrderId('test-order-invoice');
        $group->setUserId('test-user-id-3');
        $manager->persist($group);
        $this->setReference('firstqgroup', $group);

        $group = new FirstQGroup();
        $group->setFormDataRaw('{"loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","timezone_client":"Europe\/London"}');
        $group->setState('ORDER_DECLINED');
        $group->setOrderId('test-order-declined');
        $group->setUserId('test-user-id-4');
        $manager->persist($group);
        $this->setReference('firstqgroup', $group);

        $group = new FirstQGroup();
        $group->setFormDataRaw('{"loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","timezone_client":"Europe\/London"}');
        $group->setState('LIMESURVEY_PENDING');
        $group->setOrderId('test-limesurvey-pending');
        $group->setUserId('test-user-id-5');
        $manager->persist($group);
        $this->setReference('firstqgroup', $group);

        $group = new FirstQGroup();
        $group->setFormDataRaw('{"loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","timezone_client":"Europe\/London"}');
        $group->setState('LIMESURVEY_CREATED');
        $group->setOrderId('test-limesurvey-created');
        $group->setUserId('test-user-id-6');
        $manager->persist($group);
        $this->setReference('firstqgroup', $group);

        $group = new FirstQGroup();
        $group->setFormDataRaw('{"loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","timezone_client":"Europe\/London"}');
        $group->setState('RPANEL_COMPLETE');
        $group->setOrderId('test-rpanel-complete');
        $group->setUserId('test-user-id-7');
        $manager->persist($group);
        $this->setReference('firstqgroup', $group);

        $group = new FirstQGroup();
        $group->setFormDataRaw('{"loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","timezone_client":"Europe\/London"}');
        $group->setState('LIMESURVEY_COMPLETE');
        $group->setOrderId('test-limesurvey-pending');
        $group->setUserId('test-user-id-8');
        $manager->persist($group);
        $this->setReference('firstqgroup', $group);

        $group = new FirstQGroup();
        $group->setFormDataRaw('{"loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","timezone_client":"Europe\/London"}');
        $group->setState('EMAIL_SENT');
        $group->setOrderId('test-email-sent');
        $group->setUserId('test-user-id-9');
        $manager->persist($group);
        $this->setReference('firstqgroup', $group);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
