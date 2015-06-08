<?php

namespace PSL\ClipperBundle\Command;

// contrib
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Bigcommerce\Api\Client as Bigcommerce;
use \Exception as Exception;

// custom
use PSL\ClipperBundle\Utils\LimeSurvey as LimeSurvey;

class ClipperCommand extends ContainerAwareCommand
{

  private $products = array();
  private $product_ids = array();
  private $to_state;
  private $from_state;
  private $query_result;
  private $logger;

  protected function configure()
  {
    $this->setName('clipper:cron')->setDescription('Get FirstQ orders from BigCommerce and process them.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->logger = $this->getContainer()->get('monolog.logger.clipper');
    
    try {
      $params = $this->getContainer()->getParameter('clipper');
  
      $this
        ->getCompletedOrderProducts()                                 
          ->getProductIds($this->products)                            
          ->fromState($params['state_codes']['bigcommerce_pending'])  
          ->toState($params['state_codes']['bigcommerce_complete'])   
          ->changeState()                                             
        ->createLimeSurvey()                                          
          // ->fromState($params['state_codes']['bigcommerce_complete'])
          // ->toState($params['state_codes']['limesurvey_created'])
          // ->productIds($this->products)
          // ->changeState()
        ;
    }
    catch (\Exception $e) {
      $debug = array(
        "File: {$e->getFile()}",
        "Line: {$e->getLine()}",
      );
      $this->logger->debug(implode(" | ", $debug));
      $this->logger->error($e->__toString());
    }

  }

  private function fromState($state)
  {
    $this->from_state = $state;
    return $this;
  }

  private function toState($state)
  {
    $this->to_state = $state;
    return $this;
  }

  private function getProductIds($products)
  {
    $this->product_ids = array_keys($products);
    return $this;
  }

  private function changeState()
  {
    $em = $this->getContainer()->get('doctrine')->getManager();
    $fqs = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQProject')->findByIdsAndState($this->product_ids, $this->from_state);
    if ($count = count($fqs)) {
      $this->logger->debug("Found [{$count}] FirstQProject(s) with state [{$this->from_state}]", array('changeState'));
      foreach ( $fqs as $fq ) {
        $fq->setState($this->to_state);
      }
      $em->flush();
      $em->clear();
    }
    else {
      $this->logger->debug("No Products found with state [{$this->from_state}]", array('changeState'));
    }

    return $this;
  }
  
  private function getCompletedOrderProducts()
  {
    $completed_order_products = array();

    $params = $this->getContainer()->getParameter('bigcommerce');

    Bigcommerce::failOnError();
    Bigcommerce::configure(array(
      'username' => $params['api']['username'],
      'store_url' => $params['api']['store_url'],
      'api_key' => $params['api']['api_key']
    ));
    // look for orders by product id and mark them as complete
    $fields = array(
      'status_id' => $params['order_status_code_completed'],
    );
    $orders = Bigcommerce::getOrders($fields);
    if ($count = count($orders)) {
      $this->logger->debug("Found [{$count}] Completed Order(s) in BigCommerce.", array('getCompletedOrderProducts'));
      foreach ( $orders as $order ) {
        $products = Bigcommerce::getOrderProducts($order->id);
        foreach ( $products as $product ) {
          $completed_order_products[$product->product_id] = $product;
        }
      }
    }
    else {
      $this->logger->debug("No Orders found with status [{$params['order_status_code_completed']}] in BigCommerce.", array('getCompletedOrderProducts'));
    }
    $this->products = $completed_order_products;

    return $this;
  }

  private function createLimeSurvey()
  {
    $params = $this->getContainer()->getParameter('limesurvey');
    LimeSurvey::configure(array(
      'ls_baseurl' => $params['api']['ls_baseurl'],
      'ls_password' => $params['api']['ls_password'],
      'ls_user' => $params['api']['ls_user']
    ));
    
    $params = $this->getContainer()->getParameter('clipper');
    $em = $this->getContainer()->get('doctrine')->getManager();
    $fqs = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQProject')->findByState($params['state_codes']['bigcommerce_complete']);
    if ($count = count($fqs)) {
      $this->logger->debug("Found [{$count}] FirstQProject(s) with state [{$params['state_codes']['bigcommerce_complete']}.", array('createLimeSurvey'));
      //...
      
    }
    else {
      //...
      
    }
    
  }

}
