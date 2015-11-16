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
        $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
        $group->setState('ORDER_PENDING');
        $group->setOrderId('test-order-pending');
        $group->setUserId('test-user-id-1');
        $this->setReference('firstqgroup-2', $group);
        $manager->persist($group);

        $group = new FirstQGroup();
        $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
        $group->setState('ORDER_COMPLETE');
        $group->setOrderId('test-order-complete');
        $group->setUserId('250199');
        $this->setReference('firstqgroup-1', $group);
        $manager->persist($group);

        $group = new FirstQGroup();
        $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
        $group->setState('ORDER_INVOICE');
        $group->setOrderId('test-order-invoice');
        $group->setUserId('test-user-id-3');
        $manager->persist($group);

        $group = new FirstQGroup();
        $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
        $group->setState('ORDER_DECLINED');
        $group->setOrderId('test-order-declined');
        $group->setUserId('test-user-id-4');
        $manager->persist($group);

        $group = new FirstQGroup();
        $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
        $group->setState('LIMESURVEY_PENDING');
        $group->setOrderId('test-limesurvey-pending');
        $group->setUserId('test-user-id-5');
        $manager->persist($group);

        $group = new FirstQGroup();
        $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
        $group->setState('LIMESURVEY_CREATED');
        $group->setOrderId('test-limesurvey-created');
        $group->setUserId('test-user-id-6');
        $this->setReference('firstqgroup-3', $group);
        $manager->persist($group);

        $group = new FirstQGroup();
        $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
        $group->setState('RPANEL_COMPLETE');
        $group->setOrderId('test-rpanel-complete');
        $group->setUserId('test-user-id-7');
        $manager->persist($group);

        $group = new FirstQGroup();
        $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
        $group->setState('LIMESURVEY_COMPLETE');
        $group->setOrderId('test-limesurvey-pending');
        $group->setUserId('test-user-id-8');
        $manager->persist($group);

        $group = new FirstQGroup();
        $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
        $group->setState('EMAIL_SENT');
        $group->setOrderId('test-email-sent');
        $group->setUserId('test-user-id-9');
        $manager->persist($group);
        
        $group = new FirstQGroup();
        $group->setFormDataRaw('{"survey_type":"firstview","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
        $group->setState('ORDER_COMPLETE');
        $group->setUserId('test-user-id-10');
        $group->setOrderId('test-order-complete');
        $this->setReference('firstqgroup-4', $group);
        $manager->persist($group);

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
