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
  public function getOrder()
  {
    return 1;
  }
  
  /**
   * {@inheritDoc}
   */
  public function load(ObjectManager $manager)
  {
    // new version NPS+ (FirstView NOV 2015)
    $group = new FirstQGroup();
    $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"FirstView NOV 2015","name":"FirstView NOV 2015","name_full":"FirstView NOV 2015","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["Avonex (interferon beta-1a)", "Rebif (interferon beta-1a)", "Copaxone (glatiramer acetate)", "Glatopa (glatiramer acetate injection)", "Gilenya (fingolimod)", "Betaseron (interferon beta-1b)", "Tecfidera (dimethyl fumarate)", "Aubagio (teriflunomide)", "Tysabri (natalizumab)"],"attributes":["Reduces the number/severity of relapses", "Slows the progression of disease", "Provides long term efficacy", "Provides long term safety", "A positive risk-benefit profile", "Positively impacts quality of life", "Has few side effects", "Allows for good patient compliance", "Has convenient administration", "Is cost effective", "Has novel mechanism of action"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
    $group->setState('ORDER_COMPLETE');
    $group->setOrderId('test-order-complete-firstview');
    $group->setUserId('250199');
    $this->setReference('firstqgroup-1', $group);
    $manager->persist($group);
    
    // previous version NPS+
    $group = new FirstQGroup();
    $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
    $group->setState('ORDER_COMPLETE');
    $group->setOrderId('test-order-complete');
    $group->setUserId('user-1');
    $this->setReference('firstqgroup-2', $group);
    $manager->persist($group);
    
    /**
     * Added for test unit
     */
    $group = new FirstQGroup();
    $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
    $group->setState('ORDER_PENDING');
    $group->setOrderId('test-order-pending');
    $group->setUserId('user-test-001');
    $this->setReference('firstqgroup-test-002', $group);
    $manager->persist($group);

    $group = new FirstQGroup();
    $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"clipper FQG fixture 1 - title","name":"clipper FQG fixture 1 - name","name_full":"clipper FQG fixture 1 - name_full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
    $group->setState('ORDER_PENDING');
    $group->setOrderId('test-order-pending');
    $group->setUserId('test-user-id-1');
    $this->setReference('firstqgroup-2', $group);
    $manager->persist($group);

    $group = new FirstQGroup();
    $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
    $group->setState('ORDER_COMPLETE');
    $group->setOrderId('test-order-complete');
    $group->setUserId('user-test-002');
    $this->setReference('firstqgroup-test-001', $group);
    $manager->persist($group);

    $group = new FirstQGroup();
    $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
    $group->setState('ORDER_INVOICE');
    $group->setOrderId('test-order-invoice');
    $group->setUserId('user-test-003');
    $manager->persist($group);

    $group = new FirstQGroup();
    $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
    $group->setState('ORDER_DECLINED');
    $group->setOrderId('test-order-declined');
    $group->setUserId('user-test-004');
    $manager->persist($group);

    $group = new FirstQGroup();
    $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
    $group->setState('LIMESURVEY_PENDING');
    $group->setOrderId('test-limesurvey-pending');
    $group->setUserId('user-test-005');
    $manager->persist($group);

    $group = new FirstQGroup();
    $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
    $group->setState('LIMESURVEY_CREATED');
    $group->setOrderId('test-limesurvey-created');
    $group->setUserId('user-test-006');
    $this->setReference('firstqgroup-test-003', $group);
    $manager->persist($group);

    $group = new FirstQGroup();
    $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
    $group->setState('RPANEL_COMPLETE');
    $group->setOrderId('test-rpanel-complete');
    $group->setUserId('user-test-007');
    $manager->persist($group);

    $group = new FirstQGroup();
    $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
    $group->setState('LIMESURVEY_COMPLETE');
    $group->setOrderId('test-limesurvey-pending');
    $group->setUserId('user-test-008');
    $manager->persist($group);

    $group = new FirstQGroup();
    $group->setFormDataRaw('{"survey_type":"nps_plus","loi":10,"ir":10,"title":"a title","name":"a name","name_full":"a name full","patient_type":"sick","num_participants":35,"timestamp":"1436452135","markets":["USA"],"specialties":["Oncology","Cardiology"],"brands":["AA-123","BB-456","CC-789","DD-123","EE-456","FF-789"],"attributes":["it just works","painfull side effects","risk of death","just painful","mildly pointless","kind of cool","not effective","gives headaches"],"launch_date":"2015-07-22 11:10:33","completion_date":"2015-07-27 11:10:33","timezone_client":"Europe\/London","price_total":12345,"project_number":"2468","vat_number":"1359"}');
    $group->setState('EMAIL_SENT');
    $group->setOrderId('test-email-sent');
    $group->setUserId('user-test-009');
    $manager->persist($group);
    
    $manager->flush();
  }
}
