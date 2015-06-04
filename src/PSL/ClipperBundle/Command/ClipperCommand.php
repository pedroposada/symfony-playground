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
      $this
        ->setName('clipper:process-fq-orders')
        ->setDescription('Get FirstQ orders from BigCommerce and process them.')
      ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      /**
       * bigcommerce_pending
       */
      // fetch product ids from db with state 'bigcommerce_pending'
      // $em = $this->getContainer()->get('doctrine')->getManager();
      // $fqs = $em->getRepository('PSLClipperBundle:FirstQProject')->findBy(
        // array('state' => $parameters_clipper['state_codes']['bigcommerce_pending'])
      // );
      // $text = $fqs; // DEBUG
       
       
      // connect to bigcommerce to find completed orders product ids
      // if (count($fqs)) {
        // $product_ids = array();
        // foreach ($fqs as $fq) {
          // $product_ids[$fq->getBcProductId()] = $fq;
        // }
        
        // $products = $this->getCompletedOrderProducts(); // DEBUG
      // }
      
      /**
       * bigcommerce_complete
       */
      // update fqs in db
      
      // $fq = new FirstQProject();
      // $fq->setGuid('1111');
      // $fq->setBcClientId('2222');
      // $fq->setBcClientName('aaaaa');
      // $fq->setBcProductId('3333');
      // $fq->setFormDataRaw('4444');
      // $fq->setSheetDataRaw('5555');
      // $fq->setState('pending');
      // $manager->persist($fq);
      // $manager->flush();

      $clipper_params = $this->getContainer()->getParameter('clipper');
      $this
        ->getCompletedOrderProducts()
        ->fromState($clipper_params['state_codes']['bigcommerce_pending'])
        ->toState($clipper_params['state_codes']['bigcommerce_complete'])
        ->productIds($this->products)
        ->changeState()
      ;
      
      
      /**
       * send feedback to terminal
       */
      $output->writeln(print_r($this->last_error, 1));
    }

    private function fromState($state)
    {
      $this->$from_state = $state;
      return $this;
    }

    private function toState($state)
    {
      $this->$to_state = $state;
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
      Bigcommerce::failOnError();
      Bigcommerce::configure(array(
        'username'  => $parameters_bigcommerce['api']['username'],
        'store_url' => $parameters_bigcommerce['api']['store_url'],
        'api_key'   => $parameters_bigcommerce['api']['api_key']
      ));
      // look for orders by product id and mark them as complete
      $fields = array(
        'status_id' => $parameters_bigcommerce['order_status_code_completed'], // Completed orders
      );
      
      try{
        
        $orders = Bigcommerce::getOrders($fields);
        foreach ($orders as $order) {
          $products = Bigcommerce::getOrderProducts($order->id);
          foreach ($products as $product) {
            $completed_order_products[$product->product_id] = $product;
          }
        }
        $this->products = $completed_order_products;
      
      } catch(Bigcommerce\Api\Error $e) {
        
        $this->last_error = $e->getCode() . ' - ' . $e->getMessage();
        
      }      
      
      return $this;
    }
    
    /**
     * @param $state_code code to describe current state of fq project
     * @return $this
     */
     private function changeState() 
     {
       try {
         
         $em = $this->getContainer()->get('doctrine')->getManager();
         $qb = $em->createQueryBuilder();
         $qb
          ->update('\PSL\ClipperBundle\Entity\FirstQProject', 'fqp')
          ->set('fqp.state', ':to_state')
          ->where($qb->expr()->eq('fqp.state', ':from_state'))
          ->andWhere($qb->expr()->in('fqp.id', ':product_ids'))
          ->setParameter('to_state', $this->to_state)
          ->setParameter('from_state', $this->from_state)
          ->setParameter('product_ids', $this->product_ids)
          ;
         $query = $qb->getQuery();
         $this->query_result = $query->getResult();
       
       } catch(\Doctrine\ORM\ORMException $e){
           
         $this->last_error = $e->getCode() . ' - ' . $e->getMessage();
         
       }
       
       return $this;
     }
}