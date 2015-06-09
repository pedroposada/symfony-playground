<?php

namespace PSL\ClipperBundle\Utils;

use \Exception as Exception;
use \stdClass as stdClass;

/**
 * Helper class to communicate with the back end of RPanel 
 */
class Rpanel
{
  protected $container;
  
  /**
   * constructor
   */
  public 
  
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
   * @return mixed returns a project
   */
  public function createFeasibilityProject($proj_name, $proj_status, $created_by, $proj_type)
  {
    $conn = getConnection();
    
    return $conn->fetchAll('SELECT * FROM Agencies');
  }
  
  /**
   * function to open a connection to the RPanel DB
   */
  private function getConnection()
  {
    $config = new \Doctrine\DBAL\Configuration();
    $connectionParams = array(
        'dbname' => 'rpanel_rpanel',
        'user' => 'root',
        'password' => '',
        'host' => '127.0.0.1',
        'driver' => 'pdo_mysql',
    );
    return \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
  }
  
}
