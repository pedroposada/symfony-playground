<?php

namespace PSL\ClipperBundle\Controller;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\BrowserKit\Response;
use Bigcommerce\Api\Client as Bigcommerce;

use PSL\ClipperBundle\Entity\Repository\FirstQProjectRepository;


/**
 * Rest controller for Clipper
 */
class ClipperController extends FOSRestController
{
  
    /**
     * Retrieve a clipper.
     *
     * @ApiDoc(
     *   resource=true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *   }
     * )
     *
     *
     * @param Request $request the request object
     *
     * @return \Symfony\Component\BrowserKit\Response
     */
    public function getClipperGetAction(Request $request)
    {
      $em = $this->getDoctrine()->getEntityManager();
      
      $fq = $em->getRepository('PSLClipperBundle:FirstQProject')->getLatestFQs();
      
      return new Response($fq);
    }
  
    /**
     * Save new clipper.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *   }
     * )
     *
     *
     * @param Request $request the request object
     *
     * @return \Symfony\Component\BrowserKit\Response
     */
    public function postClipperSaveAction(Request $request)
    {
      $output = '';
      
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
        // 'status' => 'Completed', // Completed
      );
      $orders = Bigcommerce::getOrders($fields);
      foreach ($orders as $order) {
        $products = Bigcommerce::getOrderProducts($order->id);
        foreach ($products as $product) {
          $output[] = $product->product_id;
        }
      }
      
      return new Response($output);
    }

}