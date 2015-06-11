<?php
/**
 * PSL/ClipperBundle/Controller/RPanelController.php
 */
namespace PSL\ClipperBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use PSL\ClipperBundle\Entity\RPanelProject as RPanelProject;

use \Exception as Exception;
use \stdClass as stdClass;

/**
 * Helper class to communicate with the back end of RPanel 
 */
class RPanelController extends Controller
{
  private $params;
  
  function __construct($params) 
  {
    $this->params = $params;
  }
  
  /**
   * function to open a connection to the RPanel DBs
   * @param $dbconfig assoc array of database connection settings like
   *  dbname =>   name
   *  user =>     username
   *  password => password
   *  host =>     localhost
   *  driver =>   mysql
   */
  private function getConnection($dbconfig = array())
  {
    $config = new \Doctrine\DBAL\Configuration();
    return \Doctrine\DBAL\DriverManager::getConnection($dbconfig, $config);
  }
  
  /**
   * Create a feasibility project and return it.
   * 
   * @param RPanelProject $rp - a RPanel project object
   * 
   * @return A string representation of the last inserted ID. (feasibility_project.projid)
   */
  public function createFeasibilityProject(RPanelProject $rp)
  {
    $conn = $this->getConnection($this->params['databases']['translateapi']);
    $conn->insert('feasibility_project', array('proj_name' => $rp->getProjectName(), // 'Name of FirstQ project'
                                               'proj_status' => 1,                   // 1
                                               'created_by' => $rp->getCreatedBy(),  // userid
                                               'proj_type' => 1));                   // 1
    
    // returned the last inserted auto increment
    // feasibility_project.projid
    return $conn->lastInsertId();
  }
  
  /**
   * Creates a Feasibility project quota
   * 
   * @param RPanelProject $rp - a RPanel project object
   */
  public function createFeasibilityProjectQuota(RPanelProject $rp)
  {
    $conn = $this->getConnection($this->params['databases']['translateapi']);
    $conn->insert('feasibility_project_quota', array('proj_id' => $rp->getProjId(),              // feasibility_project.projid,
                                                    'respondent_req' => $rp->getFormDataByField('num_participants'),     // [Number of respondents required],
                                                    'specialty_id' => $rp->getSpecialtyId(),     // [Specialty ID from MDM],
                                                    'country' => $rp->getCountryId(),            // [Country ID from MDM],
                                                    'incidence_rate' => $rp->getIncidenceRate(), // [Incidence Rate, ask Claire, might be 100],
                                                    'length' => $rp->getLength(),                // [Length of interview, ask Claire, I think it is 5],
                                                    'target_size' => 0,                          // 0,
                                                    'target_list' => 0,                          // 0,
                                                    'feasibility_file' => 0,                     // 0,
                                                    'respondent' => 0,                           // 0,
                                                    'duration' => 0,                             // 0,
                                                    'field_duration' => $rp->getFieldDuration(),           // [Field Duration in days, most likely 1],
                                                    'r_uni_size' => $rp->getSheetDataByField('f3'),        // [Col F Row 3 in Google Sheet],
                                                    'r_uni_feasible' => $rp->getSheetDataByField('f5'),    // [Col F Row 5 in Google Sheet],
                                                    'r_guaranteed' => $rp->getSheetDataByField('f7'),      // [Col F Row 7 in Google Sheet],
                                                    'r_cr_req' => $rp->getSheetDataByField('f10'),         // [Col F Row 10 in Google Sheet],
                                                    'r_panel_handling' => $rp->getSheetDataByField('f15'), // [Col F Row 15 in Google Sheet],
                                                    'r_hono_budget' => $rp->getSheetDataByField('f16'),    // [Col F Row 16 in Google Sheet],
                                                    'r_total_panel' => $rp->getSheetDataByField('f17'),    // [Col F Row 17 in Google Sheet],
                                                    'r_fee_complete' => $rp->getSheetDataByField('f20'),   // [Col F Row 20 in Google Sheet],
                                                    'r_hono_complete' => $rp->getSheetDataByField('f21'),  // [Col F Row 21 in Google Sheet],
                                                    'r_cr_budget' => $rp->getSheetDataByField('f22'),      // [Col F Row 22 in Google Sheet],
                                                    'r_total_fielding' => $rp->getSheetDataByField('f24'), // [Col F Row 24 in Google Sheet],
                                                    'r_hono_offer' => $rp->getSheetDataByField('f26'),     // [Col F Row 26 in Google Sheet],
                                                    'r_hono_cur' => $rp->getSheetDataByField('f27'),       // [Col F Row 27 in Google Sheet],
                                                    'estimate_date' => $rp->getEstimateDate(),             // [Estimated start date],
                                                    'created_date' => $rp->getCreatedDate(),               // Now(),
                                                    'r_sample' => $rp->getSheetDataByField('f8'),          // [Col F Row 8 in Google Sheet],
                                                    'r_feasible' => $rp->getSheetDataByField('f7'),        // [Col F Row 7 in Google Sheet],
                                                    'r_panel_usage' => $rp->getSheetDataByField('f14'),    // [Col F Row 14 in Google Sheet],
                                                    'r_hono_handling' => $rp->getSheetDataByField('f15'),  // [Col F Row 15 in Google Sheet],
                                                    'r_client_cur' => $rp->getSheetDataByField('f12')));   // [Col F Row 12 in Google Sheet]);
    
  }
  
  /**
   * update a Feasibility project
   * 
   * @param RPanelProject $rp - a RPanel project object
   */
  public function updateFeasibilityProject(RPanelProject $rp)
  {
    $conn = $this->getConnection($this->params['databases']['translateapi']);
    $conn->update('feasibility_project', array('proj_status' => 2,                      // 2
                                               'launch_date' => $rp->getCreatedDate()), // Now()
                                         array('proj_id' => $rp->getProjId()));         // proj_id
  }
  
  /**
   * Creates a Feasibility project quota
   * 
   * @param RPanelProject $rp - a RPanel project object
   * @return A string representation of the last inserted ID. (PROJECT.project_sk)
   */
  public function createProject(RPanelProject $rp)
  {
    $conn = $this->getConnection($this->params['databases']['rpanel']);
    $conn->insert('PROJECT', array('project_code' => $rp->getProjId(),       // feasibility_project.projid
                                  'client_id' => $rp->getClientId(),         // [ClientID provided by Guohui]
                                  'status_id' => 1,                          // 1
                                  'pm_details' => $rp->getName(),            // feasibility_project.proj_name
                                  'brand_id' => 1,                           // 1
                                  'project_type' => $rp->getProjectType())); // 'jit'
    
    // returned the last inserted auto increment
    // PROJECT.project_sk
    return $conn->lastInsertId();
  }
  
  /**
   * Creates a Feasibility project detail
   * 
   * @param RPanelProject $rp - a RPanel project object
   */
  public function createProjectDetail(RPanelProject $rp)
  {
    $conn = $this->getConnection($this->params['databases']['rpanel']);
    $conn->insert('PROJECT_DETAIL', array('project_sk' => $rp->getProjectSK(),                      // PROJECT.project_sk,
                                          'specialty_id' => $rp->getSpecialtyId(),                  // feasibility_project_quota.specialty_id,
                                          'country_id' => $rp->getCountryId(),                      // feasibility_project_quota.country,
                                          'hono_amount' => $rp->getSheetDataByField('f26'),         // feasibility_project_quota.r_hono_offer,
                                          'hono_currency' => $rp->getSheetDataByField('f27'),       // feasibility_project_quota.r_hono_cur,
                                          'interview_length' => $rp->getLength(),                   // feasibility_project_quota.length,
                                          'email_template_id' => 0,                                 // 0,
                                          'sample_invites' => $rp->getSheetDataByField('f8'),       // feasibility_project_quota.r_sample,
                                          'quota' => $rp->getFormDataByField('num_participants'))); // feasibility_project_quota.respondent_req
    
  }
  
  /**
   * Creates a Feasibility Link type
   * 
   * @param RPanelProject $rp - a RPanel project object
   * 
   * @return A string representation of the last inserted ID. (feasibility_link_type.ltid)
   */
  public function feasibilityLinkType(RPanelProject $rp)
  {
    $conn = $this->getConnection($this->params['databases']['rpanel']);
    $conn->insert('feasibility_link_type', array('proj_id' => $rp->getProjectId(),          // feasibility_project.projid
                                                 'quote_id' => $rp->getProjectSK(),         // PROJECT.project_sk
                                                 'link_type' => $rp->getLinkType(),         // 'full'
                                                 'created_by' => $rp->getCreateBy(),        // [UserID created by Guohui]
                                                 'created_date' => $rp->getCreatedDate())); // Now()
    
    // returned the last inserted auto increment
    return $conn->lastInsertId();
  }
  
  /**
   * Creates a Feasibility link full url
   * 
   * @param RPanelProject $rp - a RPanel project object
   */
  public function feasibilityLinkFullUrl(RPanelProject $rp)
  {
    $conn = $this->getConnection($this->params['databases']['rpanel']);
    $conn->insert('feasibility_link_full_url', array('LTID' => $rp->getLTId(),            // feasibility_link_type.ltid
                                                 'LINK_URL' => $rp->getLinkUrl(),         // [Link URL from LimeSurvey]
                                                 'CREATED_DATE' => $rp->getCreatedDate(), // Now()
                                                 'CREATED_BY' => $rp->getCreatedBy()));   // [UserID created by Guohui]
  }
  
}
