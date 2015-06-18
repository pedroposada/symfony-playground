<?php
/**
 * PSL/ClipperBundle/Controller/GoogleSpeadsheetController.php
 * 
 * Google Speadsheet Controller Class
 * This is the class that controls all interactions with a Google Spreadsheet specified in the configuration
 * 
 * @version 1.0
 * @date 2015-05-27
 * 
 */

namespace PSL\ClipperBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\FileLocator;

use PSL\ClipperBundle\Entity\FeasibilityRequest;
use PSL\ClipperBundle\Utils\GoogleSheets;

use \stdClass as stdClass;
use \Exception as Exception;

class GoogleSpreadsheetController extends Controller
{
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
      // get Spreadsheet parameters
      $spreadsheet_name = $this->container->getParameter('psl_clipper.google_spreadsheets.spreadsheet_name');
      $worksheet_name = $this->container->getParameter('psl_clipper.google_spreadsheets.worksheet_name');
      // $sheet_id = $this->container->getParameter('psl_clipper.google_spreadsheets.sheet_id');
      
      // retrieve result
      $result = $sheets->batchSetGet($spreadsheet_name, $worksheet_name, $data, $return);
      
      if ($result) {
        $percent = round(($result['F8'] / $result['F3']) * 100, 2);
        $size = $result['F3'];
        
        $feasibility->feasibility = TRUE;
        $feasibility->description = 'Size of Universe Represented ' . $size . " - " . 'Percent of Universe Represented ' .  $percent . '%';
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
    // Google Spreadsheet parameters
    $client_id = $this->container->getParameter('psl_clipper.google_spreadsheets.client_id');
    $service_account_name = $this->container->getParameter('psl_clipper.google_spreadsheets.service_account_name');
    $p12_file_name = $this->container->getParameter('psl_clipper.google_spreadsheets.p12_file_name');
    $p12_file_uri = $this->returnFileUri($p12_file_name, '/Resources/config');
    
    // Google Sheets object
    return GoogleSheets::withProperties($client_id, $service_account_name, $p12_file_uri[0]);
  }
  
  /**
   * Return the URI of a file within the clipper bundle
   *
   * @param string $file_name    - The name of the file to look for.
   * @param string $folder_path  - The path of the folder within the bundle
   *
   * @return array - the full uri in an array
   */
  private function returnFileUri($file_name, $folder_path) 
  {
    $bundlePath = $this->get('kernel')->getBundle('PSLClipperBundle')->getPath();
    $directories = array($bundlePath . $folder_path);
    $locator = new FileLocator($directories);
    return $locator->locate($file_name, null, false);
  }
}
