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
use PSL\ClipperBundle\Entity\Repository\FirstQProjectRepository;

class ClipperCommand extends ContainerAwareCommand
{
    protected function configure()
    {
      $this
        ->setName('clipper:get-fq-orders')
        ->setDescription('Get FirstQ orders from BigCommerce and process them.')
      ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      // process bigcommerce_pending
      
      
      
      // connect
      Bigcommerce::configure(array(
        'username'  => $this->getContainer()->getParameter('bigcommerce.username'),
        'store_url' => $this->getContainer()->getParameter('bigcommerce.store_url'),
        'api_key'   => $this->getContainer()->getParameter('bigcommerce.api_key')
      ));
      
      // look for orders
      $orders = array();
      $fields = array(
        'status_id' => $this->getContainer()->getParameter('bigcommerce.order_status_code.Completed'), // Completed
      );
      $orders = Bigcommerce::getOrders($fields);
      foreach ($orders as $order) {
        $products = Bigcommerce::getOrderProducts($order->id);
        foreach ($products as $product) {
          $product_full = Bigcommerce::getProduct($product->product_id);
          if (isset($product_full->categories) 
            && is_array($product_full->categories) 
            && in_array($this->getContainer()->getParameter('bigcommerce.category_code.FirstQ'), $product_full->categories)) {
            $orders[$product_full->id] = $product_full->name;
          }
        }
      }
      
      // process bigcommerce_complete
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

      $output->writeln(json_encode($text));
    }
}


/**
 * $output = '';
      
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
      
      // connect
      Bigcommerce::configure(array(
        'username'  => 'pedro.posada',
        'store_url' => 'https://store-th2odj.mybigcommerce.com',
        'api_key'   => '4e3d9f3523585f0126abb48f80ac0ff544816916'
      ));
      
      // add product
      // $fields = array(
        // 'name' => 'FirstQ Project 2',
        // 'price' => '10',
        // 'categories' => array(18), // FirstQ
        // 'type' => 'digital',
        // 'availability' => 'available',
        // 'weight' => 0.0,
      // );      
      // $product = Bigcommerce::createProduct($fields);
      
      // get products
      // $fields = array(
        // // "category" => 18,
        // "category" => 'FirstQ',
      // );
      // $output = Bigcommerce::getProducts($fields);
      
      // query orders
      // $fqs = array();
      // $fields = array(
        // 'status_id' => 10, // Completed
      // );
      // $orders = Bigcommerce::getOrders($fields);
      // foreach ($orders as $order) {
        // $products = Bigcommerce::getOrderProducts($order->id);
        // // $fqs[] = $products;
        // foreach ($products as $product) {
          // $fqs[] = $product->categories;
          // // if (in_array(18, $product->categories)) {
            // // $fqs[$product->id] = $product->name;
          // // }
        // }
      // }
      // $output = $fqs;
      
      
      $fields = array(
        'status_id' => 10, // Completed
      );
      $orders = Bigcommerce::getOrders($fields);
      foreach ($orders as $order) {
        $products = Bigcommerce::getOrderProducts($order->id);
        foreach ($products as $product) {
          $product_full = Bigcommerce::getProduct($product->product_id);
          if (isset($product_full->categories) && is_array($product_full->categories) && in_array(18, $product_full->categories)) {
            $output[$product_full->id] = $product_full->name;
          }
        }
      }
 */