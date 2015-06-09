<?php
/**
 * PSL/ClipperBundle/Controller/RPanelController.php
 */
namespace PSL\ClipperBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Doctrine\DBAL;

use \Exception as Exception;
use \stdClass as stdClass;

/**
 * Helper class to communicate with the back end of RPanel 
 */
class Rpanel extends Controller
{
  /**
   * Find all agencies.
   * 
   * @return mixed
   */
  public function findAllAgencies()
  {
    $conn = $this->getConnection();
    
    return $conn->fetchAll('SELECT * FROM Agencies');
  }
  
  /**
   * Create a feasibility project and returns it.
   * 
   * @param mixed $project - A project standard class
   * 
   * @return A string representation of the last inserted ID.
   */
  public function createFeasibilityProject($project)
  {
    
    $conn = $this->getConnection();
    $conn->insert('feasibility_project', array('proj_name' => $project->name, 
                                               'proj_status' => $project->status,
                                               'created_by' => $project->created_by,
                                               'proj_type' => $project->type));
    
    // returned the last inserted auto increment
    return $conn->lastInsertId();
  }
  
  /**
   * Creates a Feasibility project quota
   * 
   * @param mixed $project - a feasibility project standard class
   * @param mixed $folio - data related to the quota in a standard class
   * @param mixed $google_sheet - all the data returned from Google Spreadsheet in a standard class
   * 
   */
  public function createFeasibilityProjectQuota($project, $folio, $google_sheet)
  {
    
    $conn = $this->getConnection();
    $conn->insert('feasibility_project_quota', array('proj_id' => $project->id,                     // feasibility_project.projid,
                                                    'respondent_req' => $folio->respondant_req,     // [Number of respondents required],
                                                    'specialty_id' => $folio->specialty_id,         // [Specialty ID from MDM],
                                                    'country' => $folio->country,                   // [Country ID from MDM],
                                                    'incidence_rate' => $folio->incident_rate,      // [Incidence Rate, ask Claire, might be 100],
                                                    'length' => $folio->length,                     // [Length of interview, ask Claire, I think it is 5],
                                                    'target_size' => $folio->target_size,           // 0,
                                                    'target_list' => $folio->target_list,           // 0,
                                                    'feasibility_file' => $folio->feasibility_file, // 0,
                                                    'respondent' => $folio->respondent,             // 0,
                                                    'duration' => $folio->duration,                 // 0,
                                                    'field_duration' => $folio->field_duration,     // [Field Duration in days, most likely 1],
                                                    'r_uni_size' => $google_sheet->results['f3'],        // [Col F Row 3 in Google Sheet],
                                                    'r_uni_feasible' => $google_sheet->results['f5'],    // [Col F Row 5 in Google Sheet],
                                                    'r_guaranteed' => $google_sheet->results['f7'],      // [Col F Row 7 in Google Sheet],
                                                    'r_cr_req' => $google_sheet->results['f10'],         // [Col F Row 10 in Google Sheet],
                                                    'r_panel_handling' => $google_sheet->results['f15'], // [Col F Row 15 in Google Sheet],
                                                    'r_hono_budget' => $google_sheet->results['f16'],    // [Col F Row 16 in Google Sheet],
                                                    'r_total_panel' => $google_sheet->results['f17'],    // [Col F Row 17 in Google Sheet],
                                                    'r_fee_complete' => $google_sheet->results['f20'],   // [Col F Row 20 in Google Sheet],
                                                    'r_hono_complete' => $google_sheet->results['F21'],  // [Col F Row 21 in Google Sheet],
                                                    'r_cr_budget' => $google_sheet->results['F22'],      // [Col F Row 22 in Google Sheet],
                                                    'r_total_fielding' => $google_sheet->results['F24'], // [Col F Row 24 in Google Sheet],
                                                    'r_hono_offer' => $google_sheet->results['F26'],     // [Col F Row 26 in Google Sheet],
                                                    'r_hono_cur' => $google_sheet->results['F27'],       // [Col F Row 27 in Google Sheet],
                                                    'estimate_date' => $folio->estimate_date,        // [Estimated start date],
                                                    'created_date' => $folio->created_date,          // Now(),
                                                    'r_sample' => $google_sheet->results['F8'],          // [Col F Row 8 in Google Sheet],
                                                    'r_feasible' => $google_sheet->results['F7'],        // [Col F Row 7 in Google Sheet],
                                                    'r_panel_usage' => $google_sheet->results['F14'],    // [Col F Row 14 in Google Sheet],
                                                    'r_hono_handling' => $google_sheet->results['F15'],  // [Col F Row 15 in Google Sheet],
                                                    'r_client_cur' => $google_sheet->results['F12']));   // [Col F Row 12 in Google Sheet]);
  }
   
   
   
   
   
   
   
   
   
     
  /**
   * function to open a connection to the RPanel DB
   */
  private function getConnection()
  {
    $config = new Configuration();
    $connectionParams = array(
        'dbname' => 'rpanel_rpanel',
        'user' => 'root',
        'password' => '',
        'host' => '127.0.0.1',
        'driver' => 'pdo_mysql',
    );
    return DriverManager::getConnection($connectionParams, $config);
  }
  
}
