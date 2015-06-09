<?php
/**
 * PSL/ClipperBundle/Controller/RPanelController.php
 */
namespace PSL\ClipperBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
   */
  private function getConnection($db = array())
  {
    $config = new \Doctrine\DBAL\Configuration();
    $connectionParams = array(
      'dbname' => $db['dbname'],
      'user' => $db['user'],
      'password' => $db['password'],
      'host' => $db['host'],
      'driver' => $db['driver'],
    );
    return \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
  }
  
  /**
   * Find all agencies.
   * 
   * @return mixed
   */
  public function findAllAgencies()
  {
    $conn = $this->getConnection($this->params['databases']['rpanel']);
    
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
    $conn = $this->getConnection($this->params['databases']['rpanel']);
    
    $conn->insert('feasibility_project', array('proj_name' => $proj_name, 
                                               'proj_status' => $proj_status,
                                               'created_by' => $created_by,
                                               'proj_type' => $proj_type));
    
    return $conn->lastInsertId();
  }
  
  
}
