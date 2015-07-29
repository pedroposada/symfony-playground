<?php

namespace PSL\ClipperBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use PSL\ClipperBundle\Listener\FqProcess;
use PSL\ClipperBundle\Event\FirstQProjectEvent;
use PSL\ClipperBundle\Utils\MDMMapping as MDMMapping;
use PSL\ClipperBundle\Utils\RPanelProject as RPanelProject;

class LimeSurveyCreated extends FqProcess
{

  public function LimeSurveyCreated(FirstQProjectEvent $event)
  {
    // get FirstQProject object
    $fq = $event->getFirstQProject();
    
    // database parameters
    $params_rp = $this->container->getParameter('rpanel');

    // set up the RPanel Project object
    // and add other values
    $rpanel_project = new RPanelProject($fq);
    $rpanel_project->setProjName('FirstQ Project ' . time());
    $rpanel_project->setProjStatus($params_rp['default_table_values']['proj_status']);
    $launch_date = $fq->getFormDataByField('launch_date');
    // Y-m-d H:i:s
    $rpanel_project->setLaunchDate($launch_date[0]);
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

    foreach ( $sheet_data as $key => $value ) {
      $gs_object = new \stdClass();

      $specialty_id = (string)$value->specialty;
      $gs_object->specialty_id = MDMMapping::map('specialties', $specialty_id);
      // $country_id = $value->market;
      // $gs_object->country_id = MDMMapping::map('countries', $country_id);
      $gs_object->country_id = 10;
      // @TODO: this is a hard coded value up until we get the proper mapping

      foreach ( $value->result as $result_key => &$result_value ) {
        $search = array('$',',');
        $result_value = str_replace($search, "", $result_value);
      }

      $gs_object->result = $value->result;
      $gs_result_array[] = $gs_object;
    }

    // Get RPanel service
    $rps = $this->container->get('rpanel');
    // connect db
    $config = new \Doctrine\DBAL\Configuration();
    $conn = \Doctrine\DBAL\DriverManager::getConnection($params_rp['databases']['translateapi'], $config);
    $conn->connect();
    // connects and immediately starts a new transaction
    $rps->setConnection($conn);

    try {
      $conn->beginTransaction();

      // Create Feasibility Project and set the project id
      $proj_id = $rps->createFeasibilityProject($rpanel_project);
      $rpanel_project->setProjId($proj_id);

      // Recursive for different google sheet result sets
      foreach ( $gs_result_array as $key => $gs ) {
        // Create Feasibility Project Quota
        $rps->createFeasibilityProjectQuota($rpanel_project, $gs);
      }

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
    $conn->connect();
    // connects and immediately starts a new transaction
    $rps->setConnection($conn);

    try {
      $conn->beginTransaction();

      // Create Project and insert project_sk
      $project_sk = $rps->createProject($rpanel_project);
      $rpanel_project->setProjectSK($project_sk);

      // Create Feasibility Link Type and insert LTId
      $ltid = $rps->feasibilityLinkType($rpanel_project);
      $rpanel_project->setLTId($ltid);

      $ls_data = $rpanel_project->getLimesurveyDataUnserialized();

      // Recursive for different google sheet result sets
      foreach ( $gs_result_array as $key => $gs ) {

        //Create Project Detail
        $rps->createProjectDetail($rpanel_project, $gs);

        // Create Feasibility Full Url
        $urls = $ls_data[$key]->urls;
        $rps->feasibilityLinkFullUrl($rpanel_project, $urls);
      }

      $conn->commit();
    }
    catch (\Exception $e) {
      $conn->rollBack();
      throw $e;
    }
  }

}
