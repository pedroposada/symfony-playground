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

namespace PSL\ClipperBundle\Controller;

use Symfony\Component\Config\FileLocator;

use PSL\ClipperBundle\Entity\FeasibilityRequest;
use PSL\ClipperBundle\Utils\GoogleSheets;

use \stdClass as stdClass;
use \Exception as Exception;

class GoogleSpreadsheetService
{
  
  protected $client_id;
  
  protected $service_account_name;
  
  protected $p12_file_name;
  
  protected $spreadsheet_name;
  
  protected $worksheet_name;
  
  protected $p12_file_path;
  
  /**
   * Constructor function 
   * 
   * @param array $params - the array of parameters for the Google Spreadsheet connection 
   */
  public function __construct($params)
  {
    $this->client_id = $params['client_id'];
    $this->service_account_name = $params['service_account_name'];
    $this->p12_file_name = $params['p12_file_name'];
    $this->spreadsheet_name = $params['spreadsheet_name'];
    $this->worksheet_name = $params['worksheet_name'];
    $this->p12_file_path = $params['p12_file_path'];
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
    
    // @TODO: might be done somewhere else, maybe in the FeasibilityRequest object 
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
      'C10' => $form_data->loi,
      'C18' => $form_data->ir,
      'C7' => $form_data->market,
      'C8' => $form_data->specialty
    );
    // cells to return
    $return = array('F3', 'F5', 'F7', 'F8', 
                    'F10', 'F12', 'F14', 'F15', 
                    'F16', 'F17', 'F20', 'F21', 
                    'F22', 'F24', 'F26', 'F27');
    
    // Google Sheets object
    $sheets = $this->setupFeasibilitySheet();
    
    if ($sheets) {
      // retrieve result
      $result = $sheets->batchSetGet($this->spreadsheet_name, $this->worksheet_name, $data, $return);
      
      if ($result) {
        $feasibility->market = $form_data->market;
        $feasibility->specialty = $form_data->specialty;
        $feasibility->feasibility = TRUE;
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
   * @return array - the full uri in an array
   */
  private function setupFeasibilitySheet() 
  {
    $p12_file_uri = $this->returnFileUri($this->p12_file_name);
    // Google Sheets object
    return GoogleSheets::withProperties($this->client_id, $this->service_account_name, $p12_file_uri[0]);
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
}
