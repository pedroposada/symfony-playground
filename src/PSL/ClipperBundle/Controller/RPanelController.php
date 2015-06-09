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
    $conn = getConnection();
    
    return $conn->fetchAll('SELECT * FROM Agencies');
  }
  
  /**
   * Create a feasibility project and returns it.
   * 
   * @param string $proj_name - Name of FirstQ project)
   * @param int $proj_status - a numeral value for the project
   * @param int $created_by - the userid who created the project
   * @param int $proj_type - a numeral value for the project type
   * 
   * @return A string representation of the last inserted ID.
   */
  public function createFeasibilityProject($proj_name, $proj_status, $created_by, $proj_type)
  {
    $conn = getConnection();
    
    $conn->insert('feasibility_project', array('proj_name' => $proj_name, 
                                               'proj_status' => $proj_status,
                                               'created_by' => $created_by,
                                               'proj_type' => $proj_type));
    
    return $conn->lastInsertId();
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
