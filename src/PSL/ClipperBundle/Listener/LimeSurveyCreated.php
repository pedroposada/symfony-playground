<?php

namespace PSL\ClipperBundle\Listener;

use \Exception as Exception;
use \stdClass as stdClass;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use PSL\ClipperBundle\Listener\FqProcess;
use PSL\ClipperBundle\Event\FirstQProjectEvent;
use PSL\ClipperBundle\Utils\MDMMapping as MDMMapping;
use PSL\ClipperBundle\Utils\RPanelProject as RPanelProject;

class LimeSurveyCreated extends FqProcess
{

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
    $rps = $this->container->get('rpanel');
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
  }

}
