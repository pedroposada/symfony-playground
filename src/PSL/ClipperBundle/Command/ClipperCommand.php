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
    protected function configure()
    {
      $this
        ->setName('clipper:process-fq-orders')
        ->setDescription('Get FirstQ orders from BigCommerce and process them.')
      ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $text = '';
      
      /**
       * bigcommerce_pending
       */
      // fetch product ids from db with state 'bigcommerce_pending'
      $repository = $this->getContainer()->get('doctrine')->getRepository('PSLClipperBundle:FirstQProject');
      $parameters_clipper = $this->getContainer()->getParameter('clipper');
      $fqs = $repository->findBy(
        array('state' => $parameters_clipper['state_codes']['bigcommerce_pending'])
      );
       
      // connect to bigcommerce to find completed orders for these product ids
      if (count($fqs)) {
        $product_ids = array();
        foreach ($fqs as $fq) {
          $product_ids[$fq->getBcProductId()] = $fq;
        }
        $parameters_bigcommerce = $this->getContainer()->getParameter('bigcommerce');
        Bigcommerce::configure(array(
          'username'  => $parameters_bigcommerce['api']['username'],
          'store_url' => $parameters_bigcommerce['api']['store_url'],
          'api_key'   => $parameters_bigcommerce['api']['api_key']
        ));
        // look for orders by product id
        $orders = array();
        $fields = array(
          'status_id' => $parameters_bigcommerce['order_status_code_completed'], // Completed orders
        );
        $orders = Bigcommerce::getOrders($fields);
        foreach ($orders as $order) {
          $products = Bigcommerce::getOrderProducts($order->id);
          foreach ($products as $product) {
            if (key_exists($product->product_id, $product_ids)) {
              
            }
          }
        }
      }
      
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

      /**
       * send feedback to terminal
       */
      $output->writeln(json_encode($text));
    }
}