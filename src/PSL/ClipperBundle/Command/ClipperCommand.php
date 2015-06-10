<?php

namespace PSL\ClipperBundle\Command;

// contrib
use \Exception as Exception;
use \stdClass as stdClass;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\LockHandler;
use Bigcommerce\Api\Client as Bigcommerce;
use Doctrine\Common\Util\Debug as Debug;

// custom
use PSL\ClipperBundle\Utils\LimeSurvey as LimeSurvey;
use PSL\ClipperBundle\Entity\FirstQProject as FirstQProject;
use PSL\ClipperBundle\Utils\Rpanel as Rpanel;

class ClipperCommand extends ContainerAwareCommand
{
  private $logger;

  protected function configure()
  {
    $this->setName('clipper:cron')->setDescription('Get FirstQ orders from BigCommerce and process them.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // create the lock
    $lock = new LockHandler('clipper:cron');
    if (!$lock->lock()) {
      $output->writeln('The command is already running in another process.');

      return 0;
    }
    
    
    $params = $this->getContainer()->getParameter('clipper');
    $this->logger = $this->getContainer()->get('monolog.logger.clipper');
    $em = $this->getContainer()->get('doctrine')->getManager();
    
    // find all except with state 'email_sent'
    $fqs = $em->getRepository('\PSL\ClipperBundle\Entity\FirstQProject')
      ->findByStateNot($params['state_codes']['email_sent']);
    
    $this->logger->info("Found [{$fqs->count()}] fq.", array('execute'));
    foreach ($fqs as $fq) {
      try {
        $this
          ->process($fq, 'bigcommerce_pending')
          ->process($fq, 'bigcommerce_complete')
          // ->process($fq, 'limesurvey_created')
          // ->process($fq, 'rpanel_complete')
          // ->process($fq, 'limesurvey_complete') // 
          ;
        
        // feedback if all is good
        $this->logger->info('OK', array('fq.id' => $fq->getId()));
      }
      catch (\Exception $e) {
        $debug = new stdClass();
        $debug->file = $e->getFile();
        $debug->line = $e->getLine();
        $this->logger->debug(Debug::toString($debug));
        $this->logger->error($e->getMessage());
      }
    }
    
    // persist data to db
    $em->flush();
    $em->clear();
  }

  /**
   * @param $fq FirstQProject entity
   * @param $state string
   */
  private function process(FirstQProject $fq, $state)
  {
    $params = $this->getContainer()->getParameter('clipper');
    // process fq only if state matches
    if ($fq->getState() == $params['state_codes'][$state]) {
      // process fq 
      $next_state = current(array_slice($params['state_codes'], array_search($state, array_keys($params['state_codes'])) + 1, 1));
      $this
        // execute call
        ->{$state}($fq)
        // and then change state to next in the list
        ->setState($next_state);
    }
    
    return $this;
  }
  
  /**
   * Ads BigCommerce Order Id to this FirstQProject
   */
  private function bigcommerce_pending(FirstQProject $fq)
  {
    $params = $this->getContainer()->getParameter('bigcommerce');

    Bigcommerce::failOnError();
    Bigcommerce::configure(array(
      'username' => $params['api']['username'],
      'store_url' => $params['api']['store_url'],
      'api_key' => $params['api']['api_key']
    ));
    // look for orders marked complete
    $fields = array(
      'status_id' => $params['order_status_code_completed'],
    );
    $orders = Bigcommerce::getOrders($fields);
    if ($count = count($orders)) {
      $this->logger
        ->debug("Found [{$count}] Completed Order(s) in BigCommerce. Status code [{$params['order_status_code_completed']}]", array('bigcommerce_pending'));
      //...
      $pids = array();
      foreach ( $orders as $order ) {
        // loop though products for each order
        $products = Bigcommerce::getOrderProducts($order->id);
        foreach ( $products as $product ) {
          $pids[$product->product_id] = $order->id;
        }
      }
      if (isset($pids[$fq->getBcProductId()])) {
        $fq->setBcOrderId($pids[$fq->getBcProductId()]);
        $this->logger->info("Found BcProductId: [{$pids[$fq->getBcProductId()]}] for fq->id: [{$fq->getId()}]");
      }
      else {
        throw new Exception("No completed order found for fq->id: [{$fq->getId()}] with prodcut id: [{$fq->getBcProductId()}]");
      }
    }
    else {
      $this->logger
        ->debug("No Completed Order(s) found with status code [{$params['order_status_code_completed']}] in BigCommerce.", array('bigcommerce_pending'));
    }

    return $fq;
  }

  /**
   * Creates LimeSurvey fo this FirstQProject
   */
  private function bigcommerce_complete(FirstQProject $fq)
  {
    // get LS settings
    $params_ls = $this->getContainer()->getParameter('limesurvey');
    
    // config connection to LS
    $ls = new LimeSurvey();
    $ls->configure(array(
      'ls_baseurl' => $params_ls['api']['ls_baseurl'],
      'ls_password' => $params_ls['api']['ls_password'],
      'ls_user' => $params_ls['api']['ls_user']
    ));
    
    // get Survey template and replace tokens
    $finder = new Finder();
    $iterator = $finder
      ->files()
      ->name($params_ls['lss']['brand_adoption']) // TODO: use $params['lss'][fq->getFormData('FirstQ Folio name”')]
      ->in($params_ls['lss']['dir'])
      ;
    $files = iterator_to_array($iterator);
    $file = current($files);
    $lss = $file->getContents();
    $tokens = array(
      '_PATIENT_TYPE_' => "Diabetes Patients",
      '_SPECIALTY_' => implode(",", array("Diabetes", "Cardiology")),
      '_MARKET_' => implode(",", array("US", "UK")),
      '_BRAND_' => $this->clipperBrands(array("Brand1", "Brand2")),
    );
    $lss = strtr($lss, $tokens);
    
    // import S into LS
    $iSurveyID = $ls->import_survey(array(
      'sImportData' => base64_encode($lss), // BASE 64 encoded data of a lss
      'sImportDataType' => 'lss', 
      'sNewSurveyName' => "Clipper test - " . $fq->getId(), 
    ));
    $this->logger->debug(Debug::toString($ls), array('bigcommerce_complete', 'import_survey'));
    if (!is_int($iSurveyID)) {
      throw new Exception("Could not import survey for fq->id: [{$fq->getId()}]");
    }
    
    // activate S
    $response = $ls->activate_survey(array(
      'iSurveyID' => $iSurveyID, 
    ));
    $this->logger->debug(Debug::toString($ls), array('bigcommerce_complete', 'activate_survey'));
    if (!isset($response['status']) || $response['status'] != 'OK') {
      throw new Exception("Could not activate survey for fq->id: [{$fq->getId()}]");
    }
    
    // save sid
    $fq->setLimesurveySid($iSurveyID);
    
    return $fq;
  }


  /**
   * Helper function to replace questions for LS Survey template 
   * @see bigcommerce_complete
   * @param $brands array of brand names
   * @return string
   */
  private function clipperBrands($brands = array()) 
  {
    $output = '';
    
    $xml = <<<XML
  
  <row>
    <qid><![CDATA[_COUNTER_]]></qid>
    <parent_qid><![CDATA[3035]]></parent_qid>
    <sid><![CDATA[723936]]></sid>
    <gid><![CDATA[175]]></gid>
    <type><![CDATA[H]]></type>
    <title><![CDATA[_SUB_QUESTION_ID_]]></title>
    <question><![CDATA[_BRAND_]]></question>
    <other><![CDATA[N]]></other>
    <mandatory><![CDATA[N]]></mandatory>
    <question_order><![CDATA[_QUESTION_ORDER_]]></question_order>
    <language><![CDATA[en]]></language>
    <scale_id><![CDATA[0]]></scale_id>
    <same_default><![CDATA[0]]></same_default>
  </row>
  
XML;
    
    $tokens = array(
      '_COUNTER_' => 3165,
      '_SUB_QUESTION_ID_' => 'SQ001',
      '_BRAND_' => '',
      '_QUESTION_ORDER_' => 1,
    );
    foreach ((array)$brands as $key => $brand) {
      $tokens['_BRAND_'] = $brand;
      $output .= strtr($xml, $tokens);
      $tokens['_COUNTER_'] += 5;
      $tokens['_QUESTION_ORDER_'] += 1;
      $tokens['_SUB_QUESTION_ID_'] = 'SQ' . str_pad($tokens['_QUESTION_ORDER_'], 3, '0', STR_PAD_LEFT);
    }
  
    return $output;
  }

}
