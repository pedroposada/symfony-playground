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
 * @TODO: tasks 
 * - return object or serialized array of data from the spreadsheet to the CLipper Controller
 * 
 **/

namespace PSL\ClipperBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\FileLocator;
use PSL\ClipperBundle\Entity\FeasibilityRequest;
use PSL\ClipperBundle\Utils\GoogleSheets;

class GoogleSpreadsheetController extends Controller
{
    /**
     * Returns data from the Feasibility sheet
     *
     * @param mixed $form_data_raw - contains different values from the submit form
     * 
     * @return 
     */
    public function requestFeasibility($form_data)
    {
        // Feasibility object
        $feasibility = new \stdClass();
        
        // @TODO: might be done somewhere else, maybe in the FeasibilityRequest object 
        // Validation of the fields
        $error_string = '';
        if (!is_numeric($form_data->loi)) {
            $error_string .= 'LOI is not a number. ';
        }
        if (!is_numeric($form_data->ir)) {
            $error_string .= 'IR is not a number. ';
        }
        if (empty($form_data->country)) {
            $error_string .= 'Country is empty. ';
        }
        if (empty($form_data->specialty)) {
            $error_string .= 'Specialty is empty.';
        }
        
        if ($error_string !== '') {
            // Throw exception if data is incorrect
            throw new \Exception($error_string);
        }
        
        // mapping of cell to data to send
        $data = array(
            'C10' => $form_data->loi,
            'C18' => $form_data->ir,
            'C7' => $form_data->country,
            'C8' => $form_data->specialty
        );
        // cells to return
        $return = array('F3', 'F8');
        
        // Google Sheets object
        $sheets = $this->setupSheets();
        
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
            }
            else {
                throw new \Exception('Error retrieving results.');
            }
        }
        else {
            throw new \Exception('Error retrieving sheet.');
        }

        return $feasibility;
    }
    
    /**
     * Renders data from the Feasibility sheet in a twig file
     * For testing purposes
     *
     * @param int $loi          - LOI number
     * @param int $ir           - IR number
     * @param string $country   - Country name
     * @param string $specialty - Specialty name
     * 
     * @return 
     */
    public function requestFeasibilityAction($loi, $ir, $country, $specialty)
    {
        
        // @TODO: might be done somewhere else, maybe in the FeasibilityRequest object 
        // Validation of the fields
        $error_string = FALSE;
        if (!is_numeric($loi)) {
            $error_string = 'LOI is not a number.';
        }
        if (!is_numeric($ir)) {
            $error_string = 'IR is not a number.';
        }
        if (empty($country)) {
            $error_string = 'Country is empty.';
        }
        if (empty($specialty)) {
            $error_string = 'Specialty is empty.';
        }
        
        if ($error_string !== FALSE) {
          $return_string = 'Error Validation';
          return $this->render('PSLClipperBundle:GoogleSpeadsheet:index.html.twig', array('return_string' => $error_string));
        }
        
        // set the FeasibilityRequest object
        $feasibility_request = new FeasibilityRequest();
        $feasibility_request->initFeasibilityRequest($loi, $ir, $country, $specialty);
        
        // Batch Set Get Parameters
        $spreadsheet_name = $this->container->getParameter('psl_clipper.google_spreadsheets.spreadsheet_name');
        $worksheet_name = $this->container->getParameter('psl_clipper.google_spreadsheets.worksheet_name');
        // $sheet_id = $this->container->getParameter('psl_clipper.google_spreadsheets.sheet_id');
        
        // mapping of cell to data to send
        $data = array(
            'C10' => $feasibility_request->getLoi(),
            'C18' => $feasibility_request->getIr(),
            'C7' => $feasibility_request->getCountry(),
            'C8' => $feasibility_request->getSpecialty()
        );
        // cells to return
        $return = array('F3', 'F8');
        
        //Google Sheets object
        $sheets = $this->setupSheets();
        
        if ($sheets) {
            
            $result = $sheets->batchSetGet($spreadsheet_name, $worksheet_name, $data, $return);
            
            if ($result) {
                $percent = round(($result['F8'] / $result['F3']) * 100, 2);
                $size = $result['F3'];
                $return_string = 'Size of Universe Represented ' . $size . " - " . 'Percent of Universe Represented ' .  $percent . '%';
            }
            else {
                $return_string = 'Error retrieving results';
            }
        }
        else {
            $return_string = 'Error retrieving sheet';
        }
        
        // render the object
        return $this->render('PSLClipperBundle:GoogleSpeadsheet:index.html.twig', array('return_string' => $return_string));
    }
    
    /**
     * Return a GoogleSheets object with all proper parameters set and ready to  
     *
     * @return array - the full uri in an array
     */
    private function setupSheets() 
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
