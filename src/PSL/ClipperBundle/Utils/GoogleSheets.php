<?php
/**
 * PSL/ClipperBundle/Utils/GoogleSheets.php
 * 
 * Google Sheets Class
 * This is the class is a Google Spreadsheet wrapper with helper functions
 * 
 * @version 1.0
 * @date 2015-05-27
 * 
 * @TODO: tasks 
 * - Handle and return proper errors or log them if possible
 * - Caching of the sheets 
 * 
 **/

namespace PSL\ClipperBundle\Utils;

use Google\Spreadsheet\Batch\BatchRequest;
use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\SpreadsheetService;
use Google\Spreadsheet\SpreadsheetFeed;

class GoogleSheets {
  
  protected $client_id = '';

  protected $service_account_name = '';

  protected $p12_file_uri = '';
  
  public $service = FALSE;

  function __construct() 
  {
      // Since the class requires parameters, 
      // it is initiated with a static class method
      // GoogleSheets::withProperties();
  }
  
  /**
   * Public Static "Constructor" function
   */
  public static function withProperties($client_id, $service_account_name, $p12_file_uri) 
  {
    $googleSheet = New GoogleSheets();
    
    // Set all properties
    $googleSheet->client_id = $client_id;
    $googleSheet->service_account_name = $service_account_name;
    $googleSheet->p12_file_uri = $p12_file_uri;
    
    $googleSheet->service = $googleSheet->getGoogleService();
    
    return $googleSheet;
  }
  
  /**
   * Connect to the Google API and get a token.
   *
   * @return object - The spreadsheet service.
   */
  private function getGoogleService() 
  {
    
    // @TODO: build caching mechanism
    /*
    $spreadsheetService = &drupal_static(__FUNCTION__);
    if (isset($spreadsheetService)) {
      return $spreadsheetService;
    }
    */
    
    // Validate that the settings are good.
    if (empty($this->client_id) || empty($this->service_account_name) || empty($this->p12_file_uri)) {
      // print error
      // $error = 'Error loading spreadsheet settings.';
      $this->service = FALSE;
      return FALSE;
    }
    
    if (!file_exists($this->p12_file_uri)) {
      // @TODO: log error
      // $error = 'P12 key file does not exist at' . $this->p12_file_uri;
      return FALSE;
    }
    
    try {
      
      $client = new \Google_Client();
      $client->setApplicationName("PSL Sheets");
      
      if (isset($_SESSION['service_token'])) {
        $client->setAccessToken($_SESSION['service_token']);
      }
      $key = file_get_contents($this->p12_file_uri);
      $cred = new \Google_Auth_AssertionCredentials(
          $this->service_account_name,
          array('https://spreadsheets.google.com/feeds'),
          $key
      );
      $client->setAssertionCredentials($cred);
      
      if ($client->getAuth()->isAccessTokenExpired()) {
        $client->getAuth()->refreshTokenWithAssertion($cred);
      }
      
      $_SESSION['service_token'] = $client->getAccessToken();

      $token = json_decode($_SESSION['service_token']);

      $accessToken = $token->access_token;
      
      $serviceRequest = new DefaultServiceRequest($accessToken);
      ServiceRequestFactory::setInstance($serviceRequest);
      
      // $spreadsheetService = new Google\Spreadsheet\SpreadsheetService();
      $spreadsheetService = new SpreadsheetService();
      
      return $spreadsheetService;
    }
    catch (Exception $e) {
      // @TODO: log error
      // $error = 'There was an error connecting to the Google API check the log for more details. Error: ' . $e->getMessage();
      return FALSE;
    }
  }

  /**
   * Returns an array of available spreadhsheets and IDs
   *
   * @param boolean $reset
   *  This resets the cache.
   *
   * @return array
   *  An array of the available spredsheets
   */
  public function getSheets($reset = FALSE) 
  {
    if (!$service = $this->getGoogleService()) {
      // no need for an error here. It's handled in the service function
      return FALSE;
    }

    // @TODO: build caching mechanism
    /*
    // How long should I cache this data?
    $cache_time = 3600; // Put this in a parameter

    if ($cache_time > 0 && !$reset) {
      Look for this data in the cache.
      $sheets = cache_get('psl_sheets:spreadsheets');
      if (isset($sheets->data)) {
        return $sheets->data;
      }
    }
    */
    $spreadsheets = $service->getSpreadsheets();
    
    $sheet_array = array();
    foreach ($spreadsheets as $sheet) {
      $id = $sheet->getId();
      $id = pathinfo($id);
      $sheet_array[$id['filename']] = $sheet->getTitle();
    }
    // cache_set('psl_sheets:spreadsheets', $sheet_array, 'cache', time() + ~$cache_time);

    return $sheet_array;
  }

  /**
   * Return the spreadsheet and cache if possible.
   *
   * @param string  $spreadsheet
   *  The spreadsheet name.
   * @param boolean $reset
   *  Get the spreadsheet without the cache.
   *
   * @return boolean|object
   *  Either false or the spreadsheet object.
   */
  public function getSpreadsheet($spreadsheet, $reset = FALSE) 
  {
    
    // @TODO: build caching mechanism
    // $spreadsheet_static = &drupal_static(__FUNCTION__);

    if (!$service = $this->getGoogleService()) {
      // no need for an error here. It's handled in the service function
      return FALSE;
    }
    
    $sheets = $this->getSheets($reset);
    $sheets_by_name = array_flip($sheets);
    
    if (isset($sheets_by_name[$spreadsheet])) {
      $id = $sheets_by_name[$spreadsheet];
    }
    elseif (isset($sheets[$spreadsheet])) {
      $id = $spreadsheet;
    }
    else {
      // @TODO: log error
      // $error = 'Could not load spreadsheet with a name of ID of '. $spreadsheet;
      return FALSE;
    }

    // Try from static var
    if (isset($spreadsheet_static[$id]) && !$reset) {
      return $spreadsheet_static[$id];
    }

    try {
      $spreadsheet_static[$id] = $service->getSpreadsheetById($id);
      return $spreadsheet_static[$id];
    }
    catch (Exception $e) {
      // @TODO: log error
      // $error = 'There was an error retrieving the spreadsheet ' . $spreadsheet . '. Error: ' . $e->getMessage()));
      return FALSE;
    }
  }

  /**
   * Return a specific worksheet from a spreadhsheet.
   *
   * @param string  $spreadsheet    - The spreadsheet object or spreadsheet name.
   * @param string  $worksheet_name - The worksheet name
   * @param boolean $reset          - Reset the cache
   *
   * @return object - The worksheet object
   */
  public function getWorksheet($spreadsheet, $worksheet_name, $reset = FALSE) 
  {
    if (is_string($spreadsheet)) {
      $spreadsheet = $this->getSpreadsheet($spreadsheet, $reset);
    }
    // @TODO: build caching mechanism
    /*
    $worksheet_static = &drupal_static(__FUNCTION__);
    if (isset($worksheet_static[$spreadsheet->getTitle()][$worksheet_name]) && !$reset) {
      return $worksheet_static[$spreadsheet->getTitle()][$worksheet_name];
    }
    */
    
    $worksheetFeed = $spreadsheet->getWorksheets();
    $worksheet = $worksheetFeed->getByTitle($worksheet_name);
    /*
    $worksheet_static[$spreadsheet->getTitle()][$worksheet_name] = $worksheet;
    
    cache the worksheet ID mapping
    $this->worksheetIdCache($spreadsheet->getTitle(), $worksheet_name, $worksheet->getWorksheetId());
    */
    return $worksheet;
  }

  /**
   * Cache the worksheet ID
   *
   * @param string $spreadsheet_name The name of the worksheet
   * @param string $worksheet_name   The worksheet Name
   * @param string $value            If set the cache will be set to this value.
   *
   * @return string
   *  The ID of the worksheet
   */
  private function worksheetIdCache($spreadsheet_name, $worksheet_name, $value = NULL) 
  {
    
    // @TODO: build caching mechanism
    /*
    $cid = 'psl_sheets:' . $spreadsheet_name . ':' . $worksheet_name . ':id';
    
    if ($value) {
      cache_set($cid, $value);
    }
    else {
      $cache = cache_get($cid);
      if (!empty($cache->data)) {
        $id = $cache->data;
      }
      else {
        $worksheet = $this->getWorksheet($spreadsheet_name, $worksheet_name);
        $id = $worksheet->getWorksheetId();
        cache_set($cid, $id);
      }
    }
    */
    
    $worksheet = $this->getWorksheet($spreadsheet_name, $worksheet_name);
    $id = $worksheet->getWorksheetId();
    
    return $id;
  }

  /**
   * Get the cellfeed for a single spreadsheet.
   *
   * @param string $worksheet_id - The worksheet ID to get the cellfeed for
   * @param        boolean       - Reset the cache
   *
   * @return CellFeed - The cell feed for the worksheet.
   */
  public function getCellFeedByID($worksheet_id, $reset = FALSE) 
  {
    if (!$service = $this->getGoogleService()) {
      // no need for an error here. It's handled in the service function
      return FALSE;
    }
    // @TODO: build caching mechanism
    /*
    $cellfeed_static = &drupal_static(__FUNCTION__);
    if (isset($cellfeed_static[$worksheet_id]) && !$reset) {
      return $cellfeed_static[$worksheet_id];
    }
    */

    $cellFeed = $service->getCellFeed($worksheet_id);
    $cellfeed_static[$worksheet_id] = $cellFeed;

    return $cellFeed;
  }

  /**
   * Get the cellfeed for a single spreadsheet.
   *
   * @param object $spreadsheet    - The spreadsheet object
   * @param string $worksheet_name - The worksheet name
   * @param        boolean         boolean         - Rset the cache
   *
   * @return CellFeed - The cell feed for the worksheet
   *
   */
  public function getCellFeed($spreadsheet, $worksheet_name, $reset = FALSE) 
  {
    $worksheet  = $this->getWorksheet($spreadsheet, $worksheet_name);
    $worksheet_id = $worksheet->getWorksheetId();
    return $this->getCellFeedByID($worksheet_name, $reset);
  }

  /**
   * Batch send values to a worksheet.
   *
   * @param string $spreadsheet_name - The name of the spreadsheet
   * @param string $worksheet_id     - the ID of the worksheet.
   * @param array  $data             - The data to send to the spreadsheet. Example
   *                                 array('A1' => 'hello', 'A2' => 'world')
   */
  public function batchSetData($spreadsheet_name, $worksheet_id, $data) 
  {
    $batch_request = new BatchRequest();
    
    $cell_feed = $this->getCellFeedByID($worksheet_id);
    
    foreach ($data as $pos => $value) {
      // Convert position from Sheet format to indexes A2 => 1, 2
      $pos = $this->convertAlphaNumPos($pos);
      $input = $cell_feed->getCell($pos['row'], $pos['col']);
      $input->setContent($value);
      $batch_request->addEntry($input);
    }
    
    $cell_feed->updateBatch($batch_request);
  }

  /**
   * @param string  $spreadsheet_name - The name of the spreadsheet
   * @param string  $worksheet_id     - The name of the worksheet ID
   * @param array   $cell_return      - The values to return
   * @param boolean $reset            - Reste the cellFeed
   *
   * @return array - The cell values requested
   */
  public function batchGetCells($spreadsheet_name, $worksheet_id, $cell_return, $reset = FALSE) 
  {
    $cell_feed = $this->getCellFeedByID($worksheet_id, $reset);
    $return = array();
    foreach ($cell_return as $pos) {
      $pos_idx = $this->convertAlphaNumPos($pos);
      $cell = $cell_feed->getCell($pos_idx['row'], $pos_idx['col']);
      $return[$pos] = $cell->getContent();
    }

    return $return;
  }

  /**
   * Single function to bulk set and get cells.
   *
   * @param string $spreadsheet_name - The name of the spreadsheet
   * @param string $worksheet_name   - The worksheet name
   * @param array  $data             - An array of the data to send.
   * @param array  $cell_return      - An array of the cells to return
   *
   * @return array  - The cell values requested
   */
  public function batchSetGet($spreadsheet_name, $worksheet_name, $data, $cell_return) 
  {
    
    // @TODO: build caching mechanism
    // $this->worksheetIdCache($spreadsheet_name, $worksheet_name);
    
    $worksheet  = $this->getWorksheet($spreadsheet_name, $worksheet_name);
    
    $worksheet_id = $worksheet->getWorksheetId();
    
    $this->batchSetData($spreadsheet_name, $worksheet_id, $data);
    
    return $this->batchGetCells($spreadsheet_name, $worksheet_id, $cell_return, TRUE);
  }

  /**
   * Conver number to standard sheet A1, B1 notation.
   *
   * @param integer $pos - The position to convert
   *
   * @return array - The row and col position.
   */
  private function convertAlphaNumPos($pos) 
  {
    $pos = strtoupper($pos);

    $col = preg_replace('/[0-9]+/', '', $pos);
    
    // @TODO add error checking.
    
    return array(
      'col' => $this->alpha2num($col),
      'row' => preg_replace('/[^0-9]+/', '', $pos),
    );
  }

  /**
   * Convert the string notation to the number
   *
   * @param $col - The column to convert (A1, C2)
   *
   * @return int - The converted column int
   */
  private function alpha2num($col) 
  {
    $col = str_pad($col,2,'0',STR_PAD_LEFT);
    $i = ($col{0} == '0') ? 0 : (ord($col{0}) - 64) * 26;
    $i += ord($col{1}) - 64;
    return $i;
  }

  /**
   * Get the cell array from the worksheet.
   *
   * @param string $spreadsheet_name The spreadsheet name
   * @param string $worksheet_name   The worksheet name
   * @param string $key              Either key the array on 'col' or 'row'
   *
   * @return array
   *  An array of the CellFeed
   */
  public function getCellArray($spreadsheet_name, $worksheet_name, $key = 'row') 
  {
    // @TODO: build caching mechanism
    /*
    $cid = "psl_sheets:cells:$spreadsheet_name:$worksheet_name";
    $cached = cache_get($cid);
    if (isset($cached->data)) {
      $values = $cached->data;
    }
    else {
      $cellFeed = $this->getCellFeed($spreadsheet_name, $worksheet_name);
      $values = array();

      foreach ($cellFeed->getEntries() as $entry) {
        $row = $entry->getRow();
        $col = $entry->getColumn();
        $values['col'][$col][$row] = $values['row'][$row][$col] = $entry->getContent();
      }

      $expire = variable_get('psl_sheets_cache_worksheet_cells', 86400);
      cache_set($cid, $values, 'cache', time() + $expire);
    }
    */
    $cellFeed = $this->getCellFeed($spreadsheet_name, $worksheet_name);
    
    $values = array();
    foreach ($cellFeed->getEntries() as $entry) {
      $row = $entry->getRow();
      $col = $entry->getColumn();
      $values['col'][$col][$row] = $values['row'][$row][$col] = $entry->getContent();
    }
    
    return $values[$key];
  }

  /**
   * Get all values from a single row from a worksheet
   *
   * @param string   $spreadsheet_name
   *  The spreadsheet name
   * @param string   $worksheet_name
   *  The worksheet name
   * @param string   $column
   *  The column to return
   * @param int      $start_row
   *  The column to start at
   * @param null|int $end_row
   *  The row to end at or NULL to return until an empty cell.
   *
   * @return array
   *  All of the values in the row.
   */
  public function getCol($spreadsheet_name, $worksheet_name, $column, $start_row = 1, $end_row = NULL) 
  {
    $column = is_numeric($column) ? $column : $this->alpha2num($column);
    $cells = $this->getCellArray($spreadsheet_name, $worksheet_name, 'col');
    $filtered = !empty($cells[$column]) ? $cells[$column] : array();
    $return = array();
    foreach ($filtered as $row => $value) {
      if ($row < $start_row || (!empty($end_row) && $row > $end_row)) continue;
      $return[] = $value;
    }

    return $return;
  }

  /**
   * Get all values from a single column from a worksheet
   *
   * @param string $spreadsheet_name
   *  The spreadsheet name
   * @param string $worksheet_name
   *  The worksheet name
   * @param string $column
   *  The column to return
   * @param int    $start_col
   *  The column to start at
   * @param null   $end_col
   *  The column to end at or NULL to return until an empty cell.
   *
   * @return array
   *  All of the values in the column.
   */
  public function getRow($spreadsheet_name, $worksheet_name, $column, $start_col = 1, $end_col = NULL) 
  {
    $start_col = is_numeric($start_col) ? $start_col : $this->alpha2num($start_col);
    if (!empty($end_col)) {
      $end_col = is_numeric($end_col) ? $end_col : $this->alpha2num($end_col);
    }
    $cells = $this->getCellArray($spreadsheet_name, $worksheet_name, 'row');
    $filtered = !empty($cells[$column]) ? $cells[$column] : array();
    $return = array();
    foreach ($filtered as $row => $value) {
      if ($row < $start_col || (!empty($end_col) && $row > $end_col)) continue;
      $return[] = $value;
    }

    return $return;
  }
  
  /**
   * Setter functions
   */
  
  public function setClientId($client_id)
  {
    $this->client_id = $client_id;
  }
  
  public function setServiceAccountName($service_account_name)
  {
      $this->service_account_name = $service_account_name;
  }
  
  public function setP12FileRri($p12_file_uri)
  {
      $this->p12_file_uri = $p12_file_uri;
  }
  
}
