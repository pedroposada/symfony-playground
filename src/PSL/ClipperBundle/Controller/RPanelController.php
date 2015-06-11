<?php
/**
 * PSL/ClipperBundle/Controller/RPanelController.php
 */
namespace PSL\ClipperBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use PSL\ClipperBundle\Entity\FirstQProject as FirstQProject;

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
   * @param FirstQProject $fq - a firstQ project object
   * 
   * @return A string representation of the last inserted ID.
   */
  public function createFeasibilityProject(FirstQProject $fq)
  {
    $conn = $this->getConnection($this->params['databases']['translateapi']);
    $conn->insert('feasibility_project', array('proj_name' => "FirstQ Project " . $fq->getFormDataByField('timestamp'),        // 'Name of FirstQ project'
                                               'proj_status' => 1,    // 1
                                               'created_by' => $fq->created_by, // userid
                                               'proj_type' => 1));      // 1
    
    // returned the last inserted auto increment
    return $conn->lastInsertId();
  }
  
  /**
   * Creates a Feasibility project quota
   * 
   * @param FirstQProject $fq - a firstQ project object
   */
  public function createFeasibilityProjectQuota(FirstQProject $fq)
  {
    $conn = $this->getConnection($this->params['databases']['translateapi']);
    $conn->insert('feasibility_project_quota', array('proj_id' => $fq->proj_id,                  // feasibility_project.projid,
                                                    'respondent_req' => $fq->respondant_req,     // [Number of respondents required],
                                                    'specialty_id' => $fq->specialty_id,         // [Specialty ID from MDM],
                                                    'country' => $fq->country,                   // [Country ID from MDM],
                                                    'incidence_rate' => $fq->incident_rate,      // [Incidence Rate, ask Claire, might be 100],
                                                    'length' => $fq->length,                     // [Length of interview, ask Claire, I think it is 5],
                                                    'target_size' => $fq->target_size,           // 0,
                                                    'target_list' => $fq->target_list,           // 0,
                                                    'feasibility_file' => $fq->feasibility_file, // 0,
                                                    'respondent' => $fq->respondent,             // 0,
                                                    'duration' => $fq->duration,                 // 0,
                                                    'field_duration' => $fq->field_duration,     // [Field Duration in days, most likely 1],
                                                    'r_uni_size' => $fq->results['f3'],          // [Col F Row 3 in Google Sheet],
                                                    'r_uni_feasible' => $fq->results['f5'],      // [Col F Row 5 in Google Sheet],
                                                    'r_guaranteed' => $fq->results['f7'],        // [Col F Row 7 in Google Sheet],
                                                    'r_cr_req' => $fq->results['f10'],           // [Col F Row 10 in Google Sheet],
                                                    'r_panel_handling' => $fq->results['f15'],   // [Col F Row 15 in Google Sheet],
                                                    'r_hono_budget' => $fq->results['f16'],      // [Col F Row 16 in Google Sheet],
                                                    'r_total_panel' => $fq->results['f17'],      // [Col F Row 17 in Google Sheet],
                                                    'r_fee_complete' => $fq->results['f20'],     // [Col F Row 20 in Google Sheet],
                                                    'r_hono_complete' => $fq->results['F21'],    // [Col F Row 21 in Google Sheet],
                                                    'r_cr_budget' => $fq->results['F22'],        // [Col F Row 22 in Google Sheet],
                                                    'r_total_fielding' => $fq->results['F24'],   // [Col F Row 24 in Google Sheet],
                                                    'r_hono_offer' => $fq->results['F26'],       // [Col F Row 26 in Google Sheet],
                                                    'r_hono_cur' => $fq->results['F27'],         // [Col F Row 27 in Google Sheet],
                                                    'estimate_date' => $fq->estimate_date,       // [Estimated start date],
                                                    'created_date' => $fq->created_date,         // Now(),
                                                    'r_sample' => $fq->results['F8'],            // [Col F Row 8 in Google Sheet],
                                                    'r_feasible' => $fq->results['F7'],          // [Col F Row 7 in Google Sheet],
                                                    'r_panel_usage' => $fq->results['F14'],      // [Col F Row 14 in Google Sheet],
                                                    'r_hono_handling' => $fq->results['F15'],    // [Col F Row 15 in Google Sheet],
                                                    'r_client_cur' => $fq->results['F12']));     // [Col F Row 12 in Google Sheet]);
    
  }
  
  /**
   * Creates a Feasibility project quota
   * 
   * @param FirstQProject $fq - a firstQ project object
   * @return PROJECT.project_sk.
   */
  public function createProject(FirstQProject $fq)
  {
    $conn = $this->getConnection($this->params['databases']['rpanel']);
    $conn->insert('PROJECT', array('project_code' => $fq->proj_id,       // feasibility_project.projid
                                  'client_id' => $fq->respondant_req,    // [ClientID provided by Guohui]
                                  'status_id' => $fq->status_id,         // 1
                                  'pm_details' => $fq->pm_details,       // feasibility_project.proj_name
                                  'brand_id' => $fq->brand_id,           // 1
                                  'project_type' => $fq->project_type)); // 'jit'
    
    // returned the last inserted auto increment
    return $conn->lastInsertId();
  }
  
  /**
   * Creates a Feasibility project detail
   * 
   * @param FirstQProject $fq - a firstQ project object
   */
  public function createProjectDetail(FirstQProject $fq)
  {
    $conn = $this->getConnection($this->params['databases']['rpanel']);
    $conn->insert('PROJECT_DETAIL', array('project_sk' => $fq->proj_id,             // PROJECT.project_sk,
                                          'specialty_id' => $fq->respondant_req,    // feasibility_project_quota.specialty_id,
                                          'country_id' => $fq->status_id,           // feasibility_project_quota.country,
                                          'hono_amount' => $fq->pm_details,         // feasibility_project_quota.r_hono_offer,
                                          'hono_currency' => $fq->brand_id,         // feasibility_project_quota.r_hono_cur,
                                          'interview_length' => $fq->project_type,  // feasibility_project_quota.length,
                                          'email_template_id' => $fq->project_type, // 0,
                                          'sample_invites' => $fq->project_type,    // feasibility_project_quota.r_sample,
                                          'quota' => $fq->project_type));           // feasibility_project_quota.respondent_req
    
  }
  
  /**
   * Creates a Feasibility Link type
   * 
   * @param FirstQProject $fq - a firstQ project object
   * 
   * @return int feasibility_link_type.ltid
   */
  public function feasibilityLinkType(FirstQProject $fq)
  {
    $conn = $this->getConnection($this->params['databases']['rpanel']);
    $conn->insert('feasibility_link_type', array('proj_id' => $fq->id,                  // feasibility_project.projid
                                                 'quote_id' => $fq->project_sk,         // PROJECT.project_sk
                                                 'link_type' => $fq->link_type,         // 'full'
                                                 'created_by' => $fq->created_by,       // [UserID created by Guohui]
                                                 'created_date' => $fq->created_date)); // Now()
    
    // returned the last inserted auto increment
    return $conn->lastInsertId();
  }
  
  /**
   * Creates a Feasibility link full url
   * 
   * @param FirstQProject $fq - a firstQ project object
   */
  public function feasibilityLinkFullUrl(FirstQProject $fq)
  {
    $conn = $this->getConnection($this->params['databases']['rpanel']);
    $conn->insert('feasibility_link_full_url', array('LTID' => $fq->id,               // feasibility_link_type.ltid
                                                 'LINK_URL' => $fq->link_url,         // [Link URL from LimeSurvey]
                                                 'CREATED_DATE' => $fq->created_date, // Now()
                                                 'CREATED_BY' => $fq->created_by));   // [UserID created by Guohui]
  }
  
}
