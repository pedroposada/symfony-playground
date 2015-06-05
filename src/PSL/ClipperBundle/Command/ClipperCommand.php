<?php

namespace PSL\ClipperBundle\Command;

// contrib
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Bigcommerce\Api\Client as Bigcommerce;

// custom

class ClipperCommand extends ContainerAwareCommand
{

  private $products = array();
  private $product_ids = array();
  private $to_state;
  private $from_state;
  private $query_result;
  private $last_error = 'OK';

  protected function configure()
  {
    $this->setName('clipper:cron')->setDescription('Get FirstQ orders from BigCommerce and process them.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $params = $this->getContainer()->getParameter('clipper');

    $this
      ->getCompletedOrderProducts()
      ->fromState($params['state_codes']['bigcommerce_pending'])
      ->toState($params['state_codes']['bigcommerce_complete'])
      ->productIds($this->products)
      // ->productIds(array(76 => 1, 47 => 1))
      ->changeState();

    $logger = $this->getContainer()->get('logger');
    $logger->info(var_dump($this->last_error, 1));
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

  private function productIds($products)
  {
    $this->product_ids = array_keys($products);
    return $this;
  }

  /**
   * @return $this
   */
  private function getCompletedOrderProducts()
  {
    $completed_order_products = array();

    $parameters_bigcommerce = $this->getContainer()->getParameter('bigcommerce');

    try {

      Bigcommerce::failOnError();
      Bigcommerce::configure(array(
        'username' => $parameters_bigcommerce['api']['username'],
        'store_url' => $parameters_bigcommerce['api']['store_url'],
        'api_key' => $parameters_bigcommerce['api']['api_key']
      ));
      // look for orders by product id and mark them as complete
      $fields = array(
        'status_id' => $parameters_bigcommerce['order_status_code_completed'],
      );
      $orders = Bigcommerce::getOrders($fields);
      foreach ( $orders as $order ) {
        $products = Bigcommerce::getOrderProducts($order->id);
        foreach ( $products as $product ) {
          $completed_order_products[$product->product_id] = $product;
        }
      }
      $this->products = $completed_order_products;

    }
    catch(Bigcommerce\Api\Error $e) {

      $this->last_error = $e->getCode() . ' - ' . $e->getMessage();

    }

    return $this;
  }

  /**
   * @return $this
   */
  private function changeState()
  {
    try {

      $em = $this->getContainer()->get('doctrine')->getManager();
      $fqs = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQProject')->findByIdsAndState($this->product_ids, $this->from_state);
      foreach ( $fqs as $fq ) {
        $fq->setState($this->to_state);
      }
      $em->flush();
      $em->clear();

    }
    catch(\Doctrine\ORM\ORMException $e) {

      $this->last_error = $e->getCode() . ' - ' . $e->getMessage();

    }

    return $this;
  }

}
