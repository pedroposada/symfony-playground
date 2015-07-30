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
use Doctrine\Common\Util\Debug as Debug;
use Doctrine\Common\Collections\ArrayCollection;
use Rhumsaa\Uuid\Uuid;
use Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

// custom
use PSL\ClipperBundle\Utils\LimeSurvey as LimeSurvey;
use PSL\ClipperBundle\Entity\FirstQGroup as FirstQGroup;
use PSL\ClipperBundle\Entity\FirstQProject as FirstQProject;
use PSL\ClipperBundle\Service\RPanelService as RPanelService;
use PSL\ClipperBundle\Utils\RPanelProject as RPanelProject;
use PSL\ClipperBundle\Utils\MDMMapping as MDMMapping;

class ClipperCommand extends ContainerAwareCommand
{
  private $logger;
  static $timestamp;

  protected function configure()
  {
    $this->setName('clipper:cron')
      ->setDescription('Get FirstQ orders and process them.')
      ->addArgument(
        'fqid',
        InputArgument::OPTIONAL,
        'FirstQ Project ID (UUID)'
      );
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
    
    // globals
    $params = $this->getContainer()->getParameter('clipper');
    $this->logger = $this->getContainer()->get('monolog.logger.clipper');
    $em = $this->getContainer()->get('doctrine')->getManager();
    
    // FirstQ Groups
    $fq_groups = new ArrayCollection();
    $fqid = $input->getArgument('fqid');
    
    if ($fqid) {
      // get single fq
      $f = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQProject')->find($fqid);
      
      if (!$f) {
        $output->writeln("Invalid fqid [{$fqid}].");
        return 0;
      }
      
      $fq_groups->add($f);
    }
    else {
      // get multiple find all except with state 'email_sent'
      $fq_groups = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQGroup')->findByState($params['state_codes']['order_complete']);
    }
    
    $this->logger->info("Found [{$fq_groups->count()}] FirstQGroup(s) for processing.", array('execute'));
    
    foreach ($fq_groups as $fqg) {
      
      // load all FirstQProjects
      $fqs = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQProject')->findByFirstQGroupUUID($fqg->getId());
      
      foreach ($fqs as $fq) {
        
        try {
          
          $this
            // process states
            ->process($fqg, $fq, 'order_complete')
            ->process($fqg, $fq, 'limesurvey_pending')
            ->process($fqg, $fq, 'limesurvey_created')
            ->process($fqg, $fq, 'rpanel_complete')
            // ->process($fqg, $fq, 'limesurvey_complete') // next state will be "email_sent"
            ;
          
          // feedback if all is good
          $this->logger->info("OK processing FirstQProject with id: [{$fq->getId()}]");
        }
        catch (\Exception $e) {
          $this->logger->debug("File: {$e->getFile()} - {$e->getLine()}");
          $this->logger->error($e->getMessage());
        }
        
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
  private function process(FirstQGroup $fqg, FirstQProject $fq, $state)
  {
    $params = $this->getContainer()->getParameter('clipper');
    // process fq only if state matches
    if ($fq->getState() == $params['state_codes'][$state]) {
      // process fq 
      $next_state = current(array_slice($params['state_codes'], array_search($state, array_keys($params['state_codes'])) + 1, 1));
      $this
        // execute call
        ->{$state}($fqg, $fq)
        // and then change state to next in the list
        ->setState($next_state);
    }
    
    return $this;
  }
  
  /**
   * state == limesurvey_pending
   * Creates LimeSurvey Survey fo this FirstQProject
   */
  public function limesurvey_pending(FirstQGroup $fqg, FirstQProject $fqp)
  {
    // get LS settings
    $params_ls = $this->getContainer()->getParameter('limesurvey');
    
    // config connection to LS
    $ls = new LimeSurvey();
    $ls->configure($params_ls['api']);
    
    // array for limesurvey data
    $ls_data_raw_array = array();
    
    // Unserialize form and sheet data
    $form_data = $fqg->getFormDataUnserialized();
    $sheet_data = $fqp->getSheetDataUnserialized();
    
    // Mapping
    $specialty_id = MDMMapping::map('specialties', (string)$sheet_data->specialty);
    // $country_id = MDMMapping::map('countries', (string)$sheet_data->market);
    $country_id = 10; // @TODO: this is a hard coded value up until we get the proper mapping
    
    // Survey data settings
    $survey_data = new stdClass();
    $survey_data->market = $specialty_id;
    $survey_data->specialty = $country_id;
    $survey_data->patients = $form_data->patient_type; // current($fqg->getFormDataByField('patient_type'));
    $survey_data->brands = $form_data->brands; // $fqg->getFormDataByField('brands'); //$this->clipperBrands($fq->getFormDataByField('brands'));
    $survey_data->attributes = $form_data->attributes; // $fqg->getFormDataByField('statements');
    $survey_data->url_exit = $this->getContainer()->getParameter('limesurvey.url_exit');
    $type = 'nps'; //$form_data->name;
    
    $sc = $this->getContainer()->get('survey_builder');
    $lss = $sc->createSurvey($type, $survey_data);
    
    // import S into LS
    $iSurveyID = $ls->import_survey(array(
      'sImportData' => base64_encode($lss), // BASE 64 encoded data of a lss
      'sImportDataType' => 'lss', 
      'sNewSurveyName' => "Clipper test - " . $fqp->getId(), 
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
    // $participants_sample = $sheet_data->participants_sample; // number of tokens (links) for participants
    $participants_sample = 2;
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
    
    $fqp->setLimesurveyDataRaw(serialize($ls_raw_data));
    
    return $fqp;
  }

  /**
   * state == limesurvey_created
   * Write data into Rpanel
   * 
   * This step inserts data into a remote database
   * The RPanel Project object is used to keep all data from step to step 
   */
  public function limesurvey_created(FirstQGroup $fqg, FirstQProject $fqp)
  {
    // database parameters
    $params_rp = $this->getContainer()->getParameter('rpanel');
    
    $form_data = $fqg->getFormDataUnserialized();
    $sheet_data = $fqp->getSheetDataUnserialized();
    
    // set up the RPanel Project object
    // and add other values
    $rpanel_project = new RPanelProject($fqp);
    $rpanel_project->setProjName('FirstQ Project ' . self::$timestamp);
    $rpanel_project->setProjStatus($params_rp['default_table_values']['proj_status']);
    $rpanel_project->setLaunchDate($form_data->launch_date); // Y-m-d H:i:s
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
    $rpanel_project->setNumParticipants($form_data->num_participants);
    $rpanel_project->setEstimateDate(date('Y-m-d H:i:s'));
    $rpanel_project->setCreatedDate(date('Y-m-d H:i:s'));
    $rpanel_project->setProjectType($params_rp['default_table_values']['project_type']);
    $rpanel_project->setLinkType($params_rp['default_table_values']['link_type']);
    
    // GS object
    $gs_object = new stdClass();
    $specialty_id = (string)$sheet_data->specialty;
    $gs_object->specialty_id = MDMMapping::map('specialties', $specialty_id);
    // $country_id = $value->market;
    // $gs_object->country_id = MDMMapping::map('countries', $country_id);
    $gs_object->country_id = 10; // @TODO: this is a hard coded value up until we get the proper mapping

    foreach ($sheet_data->result as $result_key => &$result_value) {
      $search = array('$', ',');
      $result_value = str_replace($search, "", $result_value);
    }
  
    $gs_object->result = $sheet_data->result;
      
    // Get RPanel service
    $rps = $this->getContainer()->get('rpanel');
    // connect db
    $config = new \Doctrine\DBAL\Configuration();
    $conn = \Doctrine\DBAL\DriverManager::getConnection($params_rp['databases']['translateapi'], $config);
    $conn->connect(); // connects and immediately starts a new transaction
    $rps->setConnection($conn);
 
    try {
      $conn->beginTransaction();
      
      // Create Feasibility Project (one to many)
      if (!$fqg->getProjId()) {
        $proj_id = $rps->createFeasibilityProject($rpanel_project);
        $fqg->setProjId($proj_id);
      }
      
      // set proj_id
      $rpanel_project->setProjId($fqg->getProjId());
      
      // Create Feasibility Project Quota (many to one)
      $rps->createFeasibilityProjectQuota($rpanel_project, $gs_object);
      
      // Update Feasibility Project - Launch project
      $rps->updateFeasibilityProject($rpanel_project);
      
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
    $rps->setConnection($conn);

    try {
      $conn->beginTransaction();
      
      // Create Project (one to many)
      if (!$fqg->getProjectSk()) {
        $project_sk = $rps->createProject($rpanel_project);
        $fqg->setProjectSk($project_sk);
      }
      
      // set project_sk
      $rpanel_project->setProjectSK($fqg->getProjectSk());
      
      // Create Feasibility Link Type and insert LTId
      $ltid = $rps->feasibilityLinkType($rpanel_project);
      $rpanel_project->setLTId($ltid);
      
      // Create Project Detail (many to one)
      $rps->createProjectDetail($rpanel_project, $gs_object);
      
      // Create Feasibility Full Url
      $ls_data = $rpanel_project->getLimesurveyDataUnserialized();
      $urls = $ls_data->urls;
      $rps->feasibilityLinkFullUrl($rpanel_project, $urls);
      
      $conn->commit();
    }
    catch (\Exception $e) {
      $conn->rollBack();
      throw $e;
    }
    
    return $fqp;
  }

  /**
   * state == rpanel_complete
   * Check if we have reached quota with num_participants in limesurvey
   */
  public function rpanel_complete(FirstQGroup $fqg, FirstQProject $fqp) 
  {
    
    $ls_data = $fqp->getLimesurveyDataUnserialized();
    
    // $iSurveyID = current($fq->getLimesurveyDataByField('sid'));
    $iSurveyID = $ls_data->sid;
    
    // config connection to LS
    $params_ls = $this->getContainer()->getParameter('limesurvey');
    $ls = new LimeSurvey();
    $ls->configure($params_ls['api']);
    
    // check if quota has been reached
    // $quota = $fq->getFormDataByField('num_participants'); // get total quota
    $quota = 1;
    $response = $ls->get_summary(array(
      'iSurveyID' => $iSurveyID, 
      'sStatName' => 'completed_responses', 
    ));
    if (is_array($response) && isset($response['status'])) {
      throw new Exception("Bad response from LimeSurvey with status [{$response['status']}] for fq->id: [{$fqp->getId()}] on [get_summary]");
    }
    
    // if completed is less than quota, then exit
    if ($quota > $response) {
      throw new Exception("Quota has not been reached yet for fq->id: [{$fqp->getId()}]");
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
      throw new Exception("Bad response from LimeSurvey with status [{$response['status']}] for fq->id: [{$fqp->getId()}] on [set_survey_properties]");
    }
   
    return $fqp;
  }

  /**
   * state == limesurvey_complete
   * Get results from limesurvey and send email with csv
   */
  public function limesurvey_complete(FirstQGroup $fqg, FirstQProject $fqp) 
  {
    
    // @TODO: Support multi market/specialty combo
    $ls_data = $fqp->getLimesurveyDataUnserialized();
    
    // $iSurveyID = current($fq->getLimesurveyDataByField('sid'));
    $iSurveyID = $ls_data->sid;
    
    // config connection to LS
    $params_ls = $this->getContainer()->getParameter('limesurvey');
    $ls = new LimeSurvey();
    $ls->configure($params_ls['api']);
   
    // get limesurvey results
    $response = $ls->export_responses(array(
      'iSurveyID' => $iSurveyID,
    ));
    
    if (is_array($response)) {
      throw new Exception("LS export_responses error: [{implode(', ', $response)}] for fq->id: [{$fqp->getId()}] - limesurvey_complete");
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
    
    return $fqp;
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
