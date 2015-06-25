<?php

namespace PSL\ClipperBundle\Command;

// contrib
use \Exception as Exception;
use \stdClass as stdClass;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\LockHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bigcommerce\Api\Client as Bigcommerce;
use Doctrine\Common\Util\Debug as Debug;
use Doctrine\Common\Collections\ArrayCollection;
use Rhumsaa\Uuid\Uuid;
use Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

// custom
use PSL\ClipperBundle\Utils\LimeSurvey as LimeSurvey;
use PSL\ClipperBundle\Entity\FirstQProject as FirstQProject;
use PSL\ClipperBundle\Controller\RPanelController as RPanelController;
use PSL\ClipperBundle\Utils\RPanelProject as RPanelProject;
use PSL\ClipperBundle\Utils\MDMMapping as MDMMapping;

class ClipperCommand extends ContainerAwareCommand
{
  private $logger;
  static $timestamp;

  protected function configure()
  {
    $this->setName('clipper:cron')->setDescription('Get FirstQ orders from BigCommerce and process them.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    self::$timestamp = time();
    
    // create the lock
    $lock = new LockHandler('clipper:cron');
    if (!$lock->lock()) {
      $output->writeln('The command is already running in another process.');

      return 0;
    }
    
    
    $params = $this->getContainer()->getParameter('clipper');
    $this->logger = $this->getContainer()->get('monolog.logger.clipper');
    $em = $this->getContainer()->get('doctrine')->getManager();
    
    // find all except with state 'email_sent'
    $fqs = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQProject')
      ->findByStateNot($params['state_codes']['email_sent']);
    
    $this->logger->info("Found [{$fqs->count()}] FirstQProject(s) for processing.", array('execute'));
    foreach ($fqs as $fq) {
      try {
        
        $this
          // process states
          ->process($fq, 'bigcommerce_pending')
          ->process($fq, 'bigcommerce_complete')
          ->process($fq, 'limesurvey_created')
          ->process($fq, 'rpanel_complete')
          ->process($fq, 'limesurvey_complete') // next state will be "email_sent"
          ;
        
        // feedback if all is good
        $this->logger->info("OK processing FirstQProject with id: [{$fq->getId()}]");
      }
      catch (\Exception $e) {
        $this->logger->debug("File: {$e->getFile()} - {$e->getLine()}");
        $this->logger->error($e->getMessage());
      }
    }
    
    // persist data to db
    $em->flush();
    $em->clear();
  }

  /**
   * @param $fq FirstQProject entity
   * @param $state string
   */
  private function process(FirstQProject $fq, $state)
  {
    $params = $this->getContainer()->getParameter('clipper');
    // process fq only if state matches
    if ($fq->getState() == $params['state_codes'][$state]) {
      // process fq 
      $next_state = current(array_slice($params['state_codes'], array_search($state, array_keys($params['state_codes'])) + 1, 1));
      $this
        // execute call
        ->{$state}($fq)
        // and then change state to next in the list
        ->setState($next_state);
    }
    
    return $this;
  }
  
  /**
   * state == bigcommerce_pending
   * Ads BigCommerce Order Id to this FirstQProject
   */
  public function bigcommerce_pending(FirstQProject $fq)
  {
    $params = $this->getContainer()->getParameter('bigcommerce');

    Bigcommerce::failOnError();
    Bigcommerce::configure(array(
      'username' => $params['api']['username'],
      'store_url' => $params['api']['store_url'],
      'api_key' => $params['api']['api_key']
    ));
    // look for orders marked complete
    $fields = array(
      'status_id' => $params['order_status_code_completed'],
    );
    $orders = Bigcommerce::getOrders($fields);
    if ($count = count($orders)) {
      $this->logger
        ->debug("Found [{$count}] Completed Order(s) in BigCommerce. Status code [{$params['order_status_code_completed']}]", array('bigcommerce_pending'));
      //...
      $pids = array();
      // loop though products for each order
      foreach ( $orders as $order ) {
        $products = Bigcommerce::getOrderProducts($order->id);
        foreach ( $products as $product ) {
          $pids[$product->product_id] = $order->id;
        }
      }
      
      // try to match order with product
      if (isset($pids[$fq->getBcProductId()])) {
        $fq->setBcOrderId($pids[$fq->getBcProductId()]);
        $this->logger->info("Found BcProductId: [{$pids[$fq->getBcProductId()]}] for fq->id: [{$fq->getId()}]");
      }
      else {
        // bail if no orders match this product
        throw new Exception("No completed order found for fq->id: [{$fq->getId()}] with prodcut id: [{$fq->getBcProductId()}]");
      }
    }
    else {
      $this->logger->debug("No Completed Order(s) found in BigCommerce.", array('bigcommerce_pending'));
      throw new Exception("No completed order found for fq->id: [{$fq->getId()}] with prodcut id: [{$fq->getBcProductId()}]");
    }

    return $fq;
  }

  /**
   * state == bigcommerce_complete
   * Creates LimeSurvey Survey fo this FirstQProject
   */
  public function bigcommerce_complete(FirstQProject $fq)
  {
    // get LS settings
    $params_ls = $this->getContainer()->getParameter('limesurvey');
    
    // config connection to LS
    $ls = new LimeSurvey();
    $ls->configure($params_ls['api']);
    
    // get Survey template and replace tokens
    // @TODO: replace "$params_ls['lss']['nps_plus']" dynamically with value from form_data_raw
    $lss = file_get_contents($params_ls['lss']['dir'] . '/' . $params_ls['lss']['nps_plus']);
    
    // array for limesurvey data
    $ls_data_raw_array = array();
    
    // static data for Limesurvey
    $patient_type = current($fq->getFormDataByField('patient_type'));
    $brands = $this->clipperBrands($fq->getFormDataByField('brand'));
    $url_exit = $this->getContainer()->getParameter('limesurvey.url_exit');
    
    // array for multiple Market / Specialty
    $sheet_data = $fq->getSheetDataUnserialized();
  
    foreach ($sheet_data as $key => $value) {
      $specialty_id = MDMMapping::map('specialties', (string)$value->specialty);
      // $country_id = MDMMapping::map('countries', (string)$value->market);
      $country_id = 10; // @TODO: this is a hard coded value up until we get the proper mapping
      
      $tokens = array(
        '_PATIENT_TYPE_' => $patient_type,
        '_SPECIALTY_' => $specialty_id,
        '_MARKET_' => $country_id,
        '_BRAND_' => $brands,
        '_URL_EXIT_' => $url_exit,
      );
      
      $lss = strtr($lss, $tokens);
      
      // import S into LS
      $iSurveyID = $ls->import_survey(array(
        'sImportData' => base64_encode($lss), // BASE 64 encoded data of a lss
        'sImportDataType' => 'lss', 
        'sNewSurveyName' => "Clipper test - " . $fq->getId(), 
      ));
      
      $this->logger->debug(Debug::toString($ls->client), array('bigcommerce_complete', 'import_survey'));
      if (!is_int($iSurveyID)) {
        throw new Exception("Bad response from LimeSurvey [{$response['status']}] for fq->id: [{$fq->getId()}] on [import_survey]");
      }
      
      // activate S
      $response = $ls->activate_survey(array(
        'iSurveyID' => $iSurveyID, 
      ));
      $this->logger->debug(Debug::toString($ls->client), array('bigcommerce_complete', 'activate_survey'));
      if (!isset($response['status']) || $response['status'] != 'OK') {
        throw new Exception("Bad response from LimeSurvey [{$response['status']}] for fq->id: [{$fq->getId()}] on [activate_survey]");
      }
      
      // activate tokens
      $response = $ls->activate_tokens(array(
        'iSurveyID' => $iSurveyID, 
      ));
      if (!isset($response['status']) || $response['status'] != 'OK') {
        throw new Exception("Bad response from LimeSurvey [{$response['status']}] for fq->id: [{$fq->getId()}] on [activate_tokens]");
      }
      
      // add participants
      $participants_sample = $value->participants_sample; // number of tokens (links) for participants
      // $participants_sample = 2;
      if (empty($participants_sample)) {
        throw new Exception("Empty 'participants_sample' [{$participants_sample}] for fq->id: [{$fq->getId()}] on [bigcommerce_complete]");
      }
      
      $participants = array();
      for ($i = 0; $i < $participants_sample; $i++) { 
        $participants[] = array(
          'email' => "fq{$i}@pslgroup.com",
          'lastname' => "fq{$i}",
          'firstname' => "fq{$i}",
        );
      }
      $response = $ls->add_participants(array(
        'iSurveyID' => $iSurveyID, 
        'participantData' => $participants, 
      ));
      if (is_array($response) && isset($response['status'])) {
        throw new Exception("Bad response from LimeSurvey [{$response['status']}] for fq->id: [{$fq->getId()}] on [add_participants]");
      }
      
      // save limesurvey raw data
      $ls_raw_data = new stdClass();
      $ls_raw_data->participants = $response;
      $ls_raw_data->sid = $iSurveyID; 
      $ls_raw_data->urls = $this
        ->createlimeSurveyParticipantsURLs($this->getContainer()->getParameter('limesurvey.url_redirect'), $iSurveyID, $response);
      
      $ls_data_raw_array[] = $ls_raw_data;
    }
    
    $fq->setLimesurveyDataRaw(serialize($ls_data_raw_array));
    
    return $fq;
  }

  /**
   * state == limesurvey_created
   * Write data into Rpanel
   * 
   * This step inserts data into a remote database
   * The RPanel Project object is used to keep all data from step to step 
   */
  public function limesurvey_created(FirstQProject $fq)
  {
    // database parameters
    $params_rp = $this->getContainer()->getParameter('rpanel');
 
    // set up the RPanel Project object
    // and add other values
    $rpanel_project = new RPanelProject($fq);
    $rpanel_project->setProjName('FirstQ Project ' . self::$timestamp);
    $rpanel_project->setProjStatus($params_rp['default_table_values']['proj_status']);
    $rpanel_project->setProjType($params_rp['default_table_values']['proj_type']);
    $rpanel_project->setCreatedBy($params_rp['user_id']);
    $rpanel_project->setIncidenceRate($params_rp['default_table_values']['incidence_rate']);
    $rpanel_project->setLength($params_rp['default_table_values']['length']);
    $rpanel_project->setTargetSize($params_rp['default_table_values']['target_size']);
    $rpanel_project->setTargetList($params_rp['default_table_values']['target_list']);
    $rpanel_project->setFeasibilityFile($params_rp['default_table_values']['feasibility_file']);
    $rpanel_project->setRespondent($params_rp['default_table_values']['respondent']);
    $rpanel_project->setDuration($params_rp['default_table_values']['duration']);
    $rpanel_project->setFieldDuration($params_rp['default_table_values']['field_duration']);
    $rpanel_project->setStatusId($params_rp['default_table_values']['status_id']);
    $rpanel_project->setBrandId($params_rp['default_table_values']['brand_id']);
    $rpanel_project->setEmailTemplateId($params_rp['default_table_values']['email_template_id']);
    $num_participants = $fq->getFormDataByField('num_participants');
    $rpanel_project->setNumParticipants((int)$num_participants[0]);
    $rpanel_project->setEstimateDate(date('Y-m-d H:i:s'));
    $rpanel_project->setCreatedDate(date('Y-m-d H:i:s'));
    $rpanel_project->setProjectType($params_rp['default_table_values']['project_type']);
    $rpanel_project->setLinkType($params_rp['default_table_values']['link_type']);
   
    // array for multiple Market / Specialty
    $sheet_data = $fq->getSheetDataUnserialized();
    $gs_result_array = array();
  
    foreach ($sheet_data as $key => $value) {
      $gs_object = new stdClass();
    
      $specialty_id = (string)$value->specialty;
      $gs_object->specialty_id = MDMMapping::map('specialties', $specialty_id);
      // $country_id = $value->market;
      // $gs_object->country_id = MDMMapping::map('countries', $country_id);
      $gs_object->country_id = 10; // @TODO: this is a hard coded value up until we get the proper mapping

      foreach ($value->result as $result_key => &$result_value) {
        $search = array('$', ',');
        $result_value = str_replace($search, "", $result_value);
      }
    
      $gs_object->result = $value->result;
      $gs_result_array[] = $gs_object;
    }
    
    // connect db
    $rpc = new RPanelController($params_rp);
    $config = new \Doctrine\DBAL\Configuration();
    $conn = \Doctrine\DBAL\DriverManager::getConnection($params_rp['databases']['translateapi'], $config);
    $conn->connect(); // connects and immediately starts a new transaction
    $rpc->setConnection($conn);
 
    try {
      $conn->beginTransaction();
      
      // Create Feasibility Project and set the project id
      $proj_id = $rpc->createFeasibilityProject($rpanel_project);
      $rpanel_project->setProjId($proj_id);
 
      // Recursive for different google sheet result sets
      foreach ($gs_result_array as $key => $gs) {
        // Create Feasibility Project Quota
        $rpc->createFeasibilityProjectQuota($rpanel_project, $gs);
      }
      
      // Update Feasibility Project - Launch project
      $rpc->updateFeasibilityProject($rpanel_project);
      
      $conn->commit();
    }
    catch (\Exception $e) {
      $conn->rollBack();
      throw $e;
    }
 
    // connect db
    $config = new \Doctrine\DBAL\Configuration();
    $conn = \Doctrine\DBAL\DriverManager::getConnection($params_rp['databases']['rpanel'], $config);
    $conn->connect(); // connects and immediately starts a new transaction
    $rpc->setConnection($conn);

    try {
      $conn->beginTransaction();
      
      // Create Project and insert project_sk
      $project_sk = $rpc->createProject($rpanel_project);
      $rpanel_project->setProjectSK($project_sk);
      
      // Create Feasibility Link Type and insert LTId
      $ltid = $rpc->feasibilityLinkType($rpanel_project);
      $rpanel_project->setLTId($ltid);
      
      $ls_data = $rpanel_project->getLimesurveyDataUnserialized();
      
      // Recursive for different google sheet result sets
      foreach ($gs_result_array as $key => $gs) {
        
        //Create Project Detail 
        $rpc->createProjectDetail($rpanel_project, $gs);
        
        // Create Feasibility Full Url
        $urls = $ls_data[$key]->urls;
        $rpc->feasibilityLinkFullUrl($rpanel_project, $urls);
      }
      
      $conn->commit();
    }
    catch (\Exception $e) {
      $conn->rollBack();
      throw $e;
    }
    
    return $fq;
  }

  /**
   * state == rpanel_complete
   * Check if we have reached quota with num_participants in limesurvey
   */
  public function rpanel_complete(FirstQProject $fq) 
  {
    $iSurveyID = current($fq->getLimesurveyDataByField('sid'));
      
    // config connection to LS
    $params_ls = $this->getContainer()->getParameter('limesurvey');
    $ls = new LimeSurvey();
    $ls->configure($params_ls['api']);
    
    // check if quota has been reached
    $quota = $fq->getFormDataByField('num_participants'); // get total quota
    // $quota = 1;
    $response = $ls->get_summary(array(
      'iSurveyID' => $iSurveyID, 
      'sStatName' => 'completed_responses', 
    ));
    if (is_array($response) && isset($response['status'])) {
      throw new Exception("Bad response from LimeSurvey with status [{$response['status']}] for fq->id: [{$fq->getId()}] on [get_summary]");
    }
    
    // if completed is less than quota, then exit
    if ($quota > $response) {
      throw new Exception("Quota has not been reached yet for fq->id: [{$fq->getId()}]");
    }
    
    // quota reached, expire survey
    $this->logger->debug("Quota ({$quota}) has been reached.", array('rpanel_complete'));
    $response = $ls->set_survey_properties(array(
      'iSurveyID' => $iSurveyID, 
      'aSurveySettings' => array(
        'expires' => self::$timestamp,
      ), 
    ));
    if (is_array($response) && isset($response['status'])) {
      $this->logger->debug($response['status'], array('rpanel_complete', 'set_survey_properties'));
      throw new Exception("Bad response from LimeSurvey with status [{$response['status']}] for fq->id: [{$fq->getId()}] on [set_survey_properties]");
    }
   
    return $fq;
  }

  /**
   * state == limesurvey_complete
   * Get results from limesurvey and send email with csv
   */
  public function limesurvey_complete(FirstQProject $fq) 
  {
    $iSurveyID = current($fq->getLimesurveyDataByField('sid'));
   
    // config connection to LS
    $params_ls = $this->getContainer()->getParameter('limesurvey');
    $ls = new LimeSurvey();
    $ls->configure($params_ls['api']);
   
    // get lime survey results
    $response = $ls->export_responses(array(
      'iSurveyID' => $iSurveyID,
    ));
    if (is_array($response)) {
      throw new Exception("LS export_responses error: [{implode(', ', $response)}] for fq->id: [{$fq->getId()}] - limesurvey_complete");
    }
   
    // if we get this far then send email
    $params_clip = $this->getContainer()->getParameter('clipper');
    $message = \Swift_Message::newInstance()
      ->setFrom($params_clip['email_ls_results']['from'])
      ->setTo($params_clip['email_ls_results']['to'])
      ->setSubject(strtr($params_clip['email_ls_results']['subject'], array(
        '[URL]' => $this->getContainer()->getParameter('limesurvey.url_destination_base_sid'),
        '[SID]' => $iSurveyID,
        )))
      ->setBody(strtr($params_clip['email_ls_results']['body'], array(
        '[URL]' => $this->getContainer()->getParameter('limesurvey.url_destination_base_sid'),
        '[SID]' => $iSurveyID,
        )))
      ;
      
    // attachment
    $fs = new Filesystem();
    $csv = base64_decode($response);
    try {
      $fs->dumpFile('/tmp/file.csv', $csv);
    } 
    catch (IOExceptionInterface $e) {
      throw new Exception("[limesurvey_complete] - An error occurred while creating your file at " . $e->getPath());
    }
    $message->attach(\Swift_Attachment::fromPath('/tmp/file.csv'));
    
    // send   
    $failures = array(); // addresses of failed emails
    if (!$this->getContainer()->get('mailer')->send($message, $failures)) {
      throw new Exception("[limesurvey_complete] - Failed sending email to: " . implode(', ', $failures));
    }
    $this->logger->debug("Email: [{$message->toString()}]");
    
    return $fq;
  }


  /**
   * Helper function to replace questions for LS Survey template 
   * 
   * @see bigcommerce_complete
   * @param $brands array of brand names
   * @return string
   */
  private function clipperBrands($brands = array()) 
  {
    $output = '';
    
    $xml = <<<XML
  
  <row>
    <qid><![CDATA[_COUNTER_]]></qid>
    <parent_qid><![CDATA[3035]]></parent_qid>
    <sid><![CDATA[723936]]></sid>
    <gid><![CDATA[175]]></gid>
    <type><![CDATA[H]]></type>
    <title><![CDATA[_SUB_QUESTION_ID_]]></title>
    <question><![CDATA[_BRAND_]]></question>
    <other><![CDATA[N]]></other>
    <mandatory><![CDATA[N]]></mandatory>
    <question_order><![CDATA[_QUESTION_ORDER_]]></question_order>
    <language><![CDATA[en]]></language>
    <scale_id><![CDATA[0]]></scale_id>
    <same_default><![CDATA[0]]></same_default>
  </row>
  
XML;
    
    $tokens = array(
      '_COUNTER_' => 3165,
      '_SUB_QUESTION_ID_' => 'SQ001',
      '_BRAND_' => '',
      '_QUESTION_ORDER_' => 1,
    );
    foreach ((array)$brands as $key => $brand) {
      $tokens['_BRAND_'] = $brand;
      $output .= strtr($xml, $tokens);
      $tokens['_COUNTER_'] += 5;
      $tokens['_QUESTION_ORDER_'] += 1;
      $tokens['_SUB_QUESTION_ID_'] = 'SQ' . str_pad($tokens['_QUESTION_ORDER_'], 3, '0', STR_PAD_LEFT);
    }
  
    return $output;
  }

  /**
   * Helper function for LimeSurvey URLs
   * 
   * @param $baseURL string, base URL for limesurvey surveys, settings
   * @param $sid int, limesurvey survey id, stored in FQ entity
   * @param $participants int, stored in FormDataRaw in FQ entity
   * @return array, list of URLs for r-panel participants
   */
   public function createlimeSurveyParticipantsURLs($baseURL, $sid, $participants) 
   {
     $urls = array();
     
     foreach ($participants as $participant) {
      $urls[] = strtr($baseURL, array(
        '[SID]' => $sid,
        '[LANG]' => 'en',
        '[SLUG]' => $participant['token'], 
      ));
     }
      
     return $urls;
   }
   
}
