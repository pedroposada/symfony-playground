<?php

namespace PSL\ClipperBundle\Utils;

use \Exception as Exception;
use \stdClass as stdClass;


class Rpanel
{
  public function findAllAgencies()
  {
    $config = new \Doctrine\DBAL\Configuration();
    $connectionParams = array(
        'dbname' => 'rpanel_rpanel',
        'user' => 'root',
        'password' => '',
        'host' => '127.0.0.1',
        'driver' => 'pdo_mysql',
    );
    $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
    
    return $conn->fetchAll('SELECT * FROM Agencies');
  }

}
