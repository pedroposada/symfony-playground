<?php
/**
 * PP/SampleBundle/Controller/GoogleSpreadsheetService.php
 *
 * Google Speadsheet Service Class
 * This is the class that controls all interactions with a Google Spreadsheet specified in the configuration
 *
 * @version 1.0
 * @date 2015-05-27
 *
 */

namespace PP\SampleBundle\Service;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;

use PP\SampleBundle\Entity\FeasibilityRequest;
use PP\SampleBundle\Utils\GoogleSheets;

use \stdClass as stdClass;
use \Exception as Exception;
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
  public function __construct(
  ContainerInterface $container,
  $client_id,
  $service_account_name,
  $p12_file_name,
  $spreadsheet_name,
  $worksheet_name,
  $p12_file_path
  ) {
    $this->client_id = $client_id;
    $this->service_account_name = $service_account_name;
    $this->p12_file_name = $p12_file_name;
    $this->spreadsheet_name = $spreadsheet_name;
    $this->worksheet_name = $worksheet_name;
    $this->p12_file_path = $p12_file_path;

    $this->container = $container;
  }

  /**
   * Returns data from the Feasibility sheet
   *
   * @param mixed $form_data - contains different values from the submit form
   *
   * @return feasibility object
   */
  public function requestFeasibility($form_data)
  {
    // Feasibility object
    $feasibility = new stdClass();
    
    // Validation of the fields
    $error_string = '';
    if (!is_numeric($form_data->loi)) {
      $error_string .= 'LOI is not a number. ';
    }
    if (!is_numeric($form_data->ir)) {
      $error_string .= 'IR is not a number. ';
    }
    if (empty($form_data->market)) {
      $error_string .= 'Market is empty. ';
    }
    if (empty($form_data->specialty)) {
      $error_string .= 'Specialty is empty.';
    }

    if ($error_string !== '') {
      // Throw exception if data is incorrect
      throw new Exception($error_string);
    }

    // mapping of cell to data to send
    $data = array(
      // FirstQ
      'C3' => 'FirstQ',
      'C5' => $form_data->num_participants,
      'C7' => $form_data->market,
      'C8' => $form_data->specialty,
      'C10' => $form_data->loi,
      'C18' => $form_data->ir
    );
    // cells to return
    $return = array('F3', 'F5', 'F7', 'F8',
                    'F10', 'F12', 'F14', 'F15',
                    'F16', 'F17', 'F20', 'F21',
                    'F22', 'F24', 'F26', 'F27');

    // Google Sheets object, just in case
    $this->setupFeasibilitySheet();

    if ($this->sheet) {
      // retrieve result
      $result = $this->sheet->batchSetGet($this->spreadsheet_name, $this->worksheet_name, $data, $return);

      if ($result) {
        
        // Clean numbers except F12, F21, F22, F26, F27
        $arrayToFormat = array('F3', 'F5', 'F7', 'F8', 'F10', 'F14', 'F15', 'F16', 'F17', 'F20', 'F24');
        foreach ($result as $key => $value) {
          if (in_array($key, $arrayToFormat)) {
            // returns 
            $result[$key] = $this->returnInteger($value);
          }
        }
        
        $feasibility->market = $form_data->market;
        $feasibility->specialty = $form_data->specialty;
        $feasibility->feasibility = TRUE;
        $feasibility->num_participants = $form_data->num_participants;
        $feasibility->participants_sample = $result['F8'];
        $feasibility->price = $result['F24'];
        $feasibility->result = $result;
      }
      else {
          throw new Exception('Error retrieving results.');
      }
    }
    else {
      throw new Exception('Error retrieving sheet.');
    }

    return $feasibility;
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
}
