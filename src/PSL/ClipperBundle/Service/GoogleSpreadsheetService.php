<?php
/**
 * PSL/ClipperBundle/Controller/GoogleSpreadsheetService.php
 *
 * Google Speadsheet Service Class
 * This is the class that controls all interactions with a Google Spreadsheet specified in the configuration
 *
 * @version 1.0
 * @date 2015-05-27
 *
 */

namespace PSL\ClipperBundle\Service;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use PSL\ClipperBundle\Entity\FeasibilityRequest;
use PSL\ClipperBundle\Utils\GoogleSheets;

use \stdClass;
use \Exception;
use \DateTime;
use \DateInterval;

class GoogleSpreadsheetService
{

  protected $container;

  protected $client_id;

  protected $service_account_name;

  protected $p12_file_name;

  protected $spreadsheet_name;

  protected $worksheet_name;

  protected $p12_file_path;

  protected $sheet = FALSE;

  private $auth_cache_key = 'gdoc-client-auth-service-token';

  /**
   * Constructor function
   *
   * @param array $params - the array of parameters for the Google Spreadsheet connection
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  /**
   * Returns data from the Feasibility sheet
   *
   * @param mixed $form_data - contains different values from the submit form
   *
   * @return feasibility object
   */
  public function requestFeasibility($form_data_objects)
  {
    // Feasibility Array
    $feasibility_array = array();
    
    // Data arrays
    $data_in = array();
    $data_out = array();
    
    $col = array("D","E","F","G","H","I","J","K","L");
    
    foreach ($form_data_objects as $key => $form_data) {
      // mapping of cell to data to send
      $column = $col[$key];
      /*
      // original set of INSERT cells
      $data = array(
        // FirstQ
        "C3" => 'FirstQ',
        "C5" => $form_data->num_participants,
        "C7" => $form_data->market,
        "C8" => $form_data->specialty,
        "C10" => $form_data->loi,
        "C18" => $form_data->ir
      );
      */
      $data = array(
        // FirstQ
        "{$column}2" => 'FirstQ',
        "{$column}4" => $form_data->market,
        "{$column}5" => $form_data->specialty,
        "{$column}6" => $form_data->num_participants,
        "{$column}7" => $form_data->loi,
        "{$column}12" => $form_data->ir
      );
      $data_in[] = $data;
      
      // cells to return
      /*
      // original set of OUPUT cells
      $return = array("F3","F5","F7","F8","F10","F12",
                      "F14","F15","F16","F17","F20","F21",
                      "F22","F24","F26","F27");
      */
      $return = array("{$column}3","{$column}5","{$column}7","{$column}8","{$column}10","{$column}12",
                      "{$column}14","{$column}15","{$column}16","{$column}17","{$column}20","{$column}21",
                      "{$column}22","{$column}24","{$column}26","{$column}27");
      
      $data_out[] = $return;
    }
    
    $browser = $this->container->get('gremo_buzz');
    
    if($browser) {
      
      // Setup buzz request
      $url = 'http://clipper.dev:8000/sheets/cells';
      $headers = array('Content-Type' => 'application/json');
      
      $payload = new stdClass();
      $payload->sheet_key = "YiS9lrYk8kTDFIwVEOXfPJ7MTnzeKThK2";
      $payload->sheet_name = "NewUI";
      $payload->cells_insert = $data_in;
      $payload->cells_return = $data_out;
      
      // retrieve result
      $jsonPayload = $this->getSerializer()->encode($payload, 'json');
      
      $response = $browser->put($url, $headers, $jsonPayload);
      
      if ($response) {
        
        if ($response->isSuccessful()) {
          
          // Decode Json
          $json_response = json_decode($response->getContent());
          
          if (!isset($json_response->content->error_message)) {
            
            $gs_results = $json_response->content->result;
            
            // create Feasibility objects
            foreach ($gs_results as $index => $gs_result) {
              
              // type cast to an array
              $gs_result = (array) $gs_result;
              /*
              // Clean numbers except F12, F21, F22, F26, F27
              $arrayToFormat = array('F3', 'F5', 'F7', 'F8', 'F10', 'F14', 'F15', 'F16', 'F17', 'F20', 'F24');
              foreach ($gs_result as $key => $value) {
                if (in_array($key, $arrayToFormat)) {
                  // returns 
                  $gs_result[$key] = $this->returnInteger($value);
                }
              }
              */
              $form_data = $form_data_objects[$index];
              
              $feasibility = new stdClass();
              $feasibility->market = $form_data->market;
              $feasibility->specialty = $form_data->specialty;
              $feasibility->feasibility = TRUE;
              $feasibility->num_participants = $form_data->num_participants;
              // $feasibility->participants_sample = $gs_result['F8'];
              // $feasibility->price = $gs_result['F24'];
              $feasibility->participants_sample = 200;
              $feasibility->price = 2000;
              $feasibility->result = $gs_result;
              
              // Add feasibility object
              $feasibility_array[] = $feasibility;
            }
          }
          else {
            
            throw new Exception('Error retrieving results. ' . $json_response->content->error_message);
          }
        }
        else {
          // Buzz Response 
          throw new Exception('Error retrieving results. Request was not Successful.');
        }
        
      }
      else {
        // Generic error
        throw new Exception('Error retrieving results.');
      }
    }
    else {
      throw new Exception('Error retrieving sheet.');
    }

    return $feasibility_array;
  }

  /**
   * Return a GoogleSheets object with all proper parameters set and ready to
   *
   * @return object - GoogleSheets object containing the full uri wrapped in protected XML object
   */
  public function setupFeasibilitySheet()
  {
    if (!empty($this->sheet)) {
      return $this->sheet;
    }

    $p12_file_uri = $this->returnFileUri($this->p12_file_name);

    //lookup for cached auth
    $dbcache     = $this->container->get('clipper_cache');
    $cached_auth = FALSE;
    if ($dbcache->is_enabled()) {
      //see 3rd argument, reject if expired
      $cached_auth = $dbcache->get($this->auth_cache_key, FALSE, TRUE);
      if (is_object($cached_auth)) {
        $cached_auth = $cached_auth->getData();
      }
      else {
        $cached_auth = ''; //natural; active but no active data
      }
    }

    // Google Sheets object
    $this->sheet = GoogleSheets::withProperties($this->client_id, $this->service_account_name, $p12_file_uri[0], $cached_auth);

    //update cache
    if (($cached_auth !== FALSE) && (!empty($this->sheet->auth_token))) {
      if ($cached_auth == $this->sheet->auth_token) {
        //if no changes, skip storage
        $this->sheet->last_messages[] = 'Auth token cache did not change.';
      }
      else {
        $token_data = json_decode($this->sheet->auth_token);
        $token_created = FALSE;
        // get google mentioned time of expiration
        if (!empty($token_data)) {
          $token_created = new DateTime();
          $token_created->setTimestamp($token_data->created);
          $expiry = new DateInterval('PT' . $token_data->expires_in . 'S');
          $token_created->add($expiry);
        }
        $res = $dbcache->set($this->auth_cache_key, $this->sheet->auth_token, $token_created);
        if ($res) {
          $this->sheet->last_messages[] = 'Auth token cache updated.';
        }
      }
    }

    return $this->sheet;
  }

  /**
   * Return the URI of a file within the clipper bundle
   *
   * @param string $file_name    - The name of the file to look for.
   * @param string $folder_path  - The path of the folder within the bundle
   *
   * @return array - the full uri in an array
   */
  private function returnFileUri($file_name)
  {
    $directories = array($this->p12_file_path);
    $locator = new FileLocator($directories);
    return $locator->locate($file_name, null, false);
  }

  /**
   * Method to return Authentication Cache name key string.
   * @method get_auth_cache_key
   *
   * @return string
   */
  public function get_auth_cache_key() {

    return $this->auth_cache_key;
  }

  /**
   * ----------------------------------------------------------------------------------------
   * Helpers
   * ----------------------------------------------------------------------------------------
   */

  /**
   * Returns an int
   */
  function returnInteger($numberString) {
    return (int)preg_replace("/[^0-9]/", "", $numberString);
  }
  
  /**
   * Serializer
   */
  protected function getSerializer()
  {
    $encoders = array(new XmlEncoder(), new JsonEncoder());
    $normalizers = array(new ObjectNormalizer());

    return new Serializer($normalizers, $encoders);
  }
}
