<?php

namespace PSL\ClipperBundle\Utils;

use \Exception as Exception;
use \stdClass as stdClass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use Doctrine\DBAL\Connection as Connection;

class Rpanel
{
  private $container;

  function __construct()
  {
    $this->container = new ContainerBuilder();
  }

  public function findAllAgencies()
  {
    // Each connection is also accessible via the doctrine.dbal.[name]_connection
    // service where [name] if the name of the connection.
    $conn = $this->container->get('doctrine.dbal.connections.rpanel');
    $agencies = $conn->fetchAll('SELECT * FROM Agencies');
  }

}
