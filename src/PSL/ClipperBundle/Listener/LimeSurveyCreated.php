<?php

namespace PSL\ClipperBundle\Listener;

use \Exception;
use \stdClass;

use Symfony\Component\DependencyInjection\ContainerInterface;

use PSL\ClipperBundle\Listener\FqProcess;
use PSL\ClipperBundle\Event\FirstQProjectEvent;
use PSL\ClipperBundle\Utils\MDMMapping;
use PSL\ClipperBundle\Utils\RPanelProject;
use PSL\ClipperBundle\Service\RPanelService;
use PSL\ClipperBundle\Utils\CountryLanguageMapping as CountryLanguageMapping;

class LimeSurveyCreated extends FqProcess
{
  private $rps;

  public function __construct(ContainerInterface $container, $state, RPanelService $rps)
  {
    parent::__construct($container, $state);
    $this->rps = $rps;
  }
  
  protected function main(FirstQProjectEvent $event)
  {
    // get FirstQGroup and FirstQProject objects
    $fqg = $event->getFirstQProjectGroup();
    $fqp = $event->getFirstQProject();

    // database parameters
    $params_rp = $this->container->getParameter('rpanel');

    $form_data = $fqg->getFormDataUnserialized();
    $sheet_data = $fqp->getSheetDataUnserialized();

    // set up the RPanel Project object
    // and add other values
    $rpanel_project = new RPanelProject($fqp);
    $rpanel_project->setProjName($form_data['title'] . ' ' . self::$timestamp);
    $rpanel_project->setProjStatus($params_rp['default_table_values']['proj_status']);
    $rpanel_project->setLaunchDate($form_data['launch_date']); // Y-m-d H:i:s
    $rpanel_project->setProjType($params_rp['default_table_values']['proj_type']);
    $rpanel_project->setCreatedBy($params_rp['user_id']);
    $rpanel_project->setClientId($params_rp['client_id']);
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
    $rpanel_project->setEstimateDate(date('Y-m-d H:i:s'));
    $rpanel_project->setCreatedDate(date('Y-m-d H:i:s'));
    $rpanel_project->setProjectType($params_rp['default_table_values']['project_type']);
    $rpanel_project->setLinkType($params_rp['default_table_values']['link_type']);
    $rpanel_project->setNumParticipants($sheet_data['num_participants']);
    $rpanel_project->setExpiredDate($rpanel_project->getAddedDuration('P5D', 'Y-m-d H:i:s'));
    $rpanel_project->setProjNum($fqg->getId());
    
    // URLs
    $redirecturl = $this->container->getParameter('limesurvey.url_redirect');
    
    // Create Feasibility Full Url
    $baseURL = $this->container->getParameter('limesurvey.url_redirect');
    $ls_data = $rpanel_project->getLimesurveyDataUnserialized();
    
    $languageCode = CountryLanguageMapping::getLanguage($sheet_data['market']);
    $lsurls = $this->createlimeSurveyParticipantsURLs($baseURL, $ls_data, $languageCode);
    
    $rpanel_project->setUrls($lsurls);
    
    // GS object
    $gs_object = new stdClass();
    $gs_object->specialty_id = MDMMapping::map('specialties', $sheet_data['specialty']);
    $gs_object->country_id = MDMMapping::map('countries', $sheet_data['market']);

    foreach ($sheet_data['result'] as $result_key => &$result_value) {
      $search = array('$', ',');
      $result_value = str_replace($search, "", $result_value);
    }

    $gs_object->result = $sheet_data['result'];

    // Get RPanel service
    $rps = $this->rps;
    // connect db
    $config = new \Doctrine\DBAL\Configuration();
    $dbconfig = array(
      'dbname' => $this->container->getParameter('rpanel.databases.translateapi.dbname'),
      'user' => $this->container->getParameter('rpanel.databases.translateapi.user'),
      'password' => $this->container->getParameter('rpanel.databases.translateapi.password'),
      'host' => $this->container->getParameter('rpanel.databases.translateapi.host'),
      'driver' => $this->container->getParameter('rpanel.databases.translateapi.driver'),
    );
    $dbconfig_validate = array_values($dbconfig);
    $dbconfig_validate = array_filter($dbconfig_validate);
    if (empty($dbconfig_validate)) {
      throw new Exception("rPanel database.translateapi parameters is missing.");
    }
    $conn_translateapi = \Doctrine\DBAL\DriverManager::getConnection($dbconfig, $config);
    $conn_translateapi->connect(); // connects and immediately starts a new transaction
    $rps->setConnection($conn_translateapi);

    try {
      $conn_translateapi->beginTransaction();

      // Create Feasibility Project (one to many)
      if (!$fqg->getProjId()) {
        $proj_id = $rps->createFeasibilityProject($rpanel_project);
        $fqg->setProjId($proj_id);
      }

      // set proj_id
      $rpanel_project->setProjId($fqg->getProjId());

      // Create Feasibility Project Quota (many to one)
      $quote_id = $rps->createFeasibilityProjectQuota($rpanel_project, $gs_object);
      $rpanel_project->setQuoteId($quote_id);

      // Update Feasibility Project - Launch project
      $rps->updateFeasibilityProject($rpanel_project);
    }
    catch (\Exception $e) {
      $conn_translateapi->rollBack();
      $message = $e->getMessage();
      throw new Exception("rPanel database connection error: (databases.translateapi) [{$message}]");
    }

    // connect db
    $config = new \Doctrine\DBAL\Configuration();
    $dbconfig = array(
      'dbname' => $this->container->getParameter('rpanel.databases.rpanel.dbname'),
      'user' => $this->container->getParameter('rpanel.databases.rpanel.user'),
      'password' => $this->container->getParameter('rpanel.databases.rpanel.password'),
      'host' => $this->container->getParameter('rpanel.databases.rpanel.host'),
      'driver' => $this->container->getParameter('rpanel.databases.rpanel.driver'),
    );
    $dbconfig_validate = array_values($dbconfig);
    $dbconfig_validate = array_filter($dbconfig_validate);
    if (empty($dbconfig_validate)) {
      throw new Exception("rPanel database.rpanel parameters is missing.");
    }
    $conn_rpanel = \Doctrine\DBAL\DriverManager::getConnection($dbconfig, $config);
    $conn_rpanel->connect(); // connects and immediately starts a new transaction
    $rps->setConnection($conn_rpanel);

    try {
      $conn_rpanel->beginTransaction();

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
      
      // feasibility_full_url - Create Feasibility Full Urls
      $rps->feasibilityLinkFullUrl($rpanel_project);

      // PROJECT_DETAIL_TEXTINVITES
      $rps->createProjectDetailTextinvites($rpanel_project);
      
      // Commit both
      $conn_translateapi->commit();
      $conn_rpanel->commit();
    }
    catch (\Exception $e) {
      // Revert both connections
      $conn_translateapi->rollBack();
      $conn_rpanel->rollBack();
      $message = $e->getMessage();
      throw new Exception("rPanel database connection error: (databases.rpanel) [{$message}]");
    }
    
  }

  /**
   * Helper function for LimeSurvey URLs
   *
   * @param $baseURL string, base URL for limesurvey surveys, settings
   * @param $data array, stored in limesurvey_data_raw in FQ entity
   * @param $languageCode string, language code for limesurvey surveys
   *
   * @return array, list of URLs for r-panel participants
   */
  private function createlimeSurveyParticipantsURLs($baseURL, $data, $languageCode) {
    
    $urls = array();
    $sid = $data['sid'];
    if (!isset($data['participants']) && isset($data['tokens'])) {
      foreach ($data['tokens'] as $token) {
        $urls[] = strtr($baseURL, array(
          '[SID]' => $sid,
          '[LANG]' => $languageCode,
          '[SLUG]' => $token,
        ));
      }
    }
    else if (isset($data['participants'])) {
      foreach ($data['participants'] as $participant) {
        $token = (isset($participant['token'])&&!empty($participant['token']))
          ? $participant['token']
          : uniqid('', true);
        $urls[] = strtr($baseURL, array(
          '[SID]' => $sid,
          '[LANG]' => $languageCode,
          '[SLUG]' => $token,
        ));
      }
    }

    return $urls;
  }

}
