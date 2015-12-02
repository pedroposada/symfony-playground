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
 **/

namespace PSL\ClipperBundle\Utils;

use Google\Spreadsheet\Batch\BatchRequest;
use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\SpreadsheetService;
use Google\Spreadsheet\SpreadsheetFeed;

use Google_Cache_Null;

class GoogleSheets {

  protected $client_id = '';

  protected $service_account_name = '';

  protected $p12_file_uri = '';

  protected $client = FALSE;

  public $service = FALSE;

  public $auth_token = FALSE;

  public $last_messages = array();

  function __construct()
  {
      // Since the class requires parameters,
      // it is initiated with a static class method
      // GoogleSheets::withProperties();
  }

  /**
   * Public Static "Constructor" function
   */
  public static function withProperties($client_id, $service_account_name, $p12_file_uri, $cached_token = FALSE)
  {
    // Set all properties
    $googleSheet = New GoogleSheets();
    $googleSheet->client_id            = $client_id;
    $googleSheet->service_account_name = $service_account_name;
    $googleSheet->p12_file_uri         = $p12_file_uri;
    $googleSheet->auth_token           = $cached_token;
    $googleSheet->service              = $googleSheet->getGoogleService();
    return $googleSheet;
  }

  /**
   * Connect to the Google API and get a token.
   *
   * @return object - The spreadsheet service.
   */
  private function getGoogleService()
  {

    static $spreadsheetService;
    if (isset($spreadsheetService)) {
      return $spreadsheetService;
    }

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
      if (empty($this->client)) {
        $this->client = new \Google_Client();
        if ($this->auth_token === FALSE) {
          $cache_class = get_class($this->client->getCache());
          $this->last_messages[] = "Default cache is enable; '{$cache_class}'.";
        }
        else {
          //overwrite - disabled default cache method (file)
          $gcache_null = new Google_Cache_Null($this->client);
          $this->client->setCache($gcache_null);
          $this->last_messages[] = 'Default cache is disabled. External cache overwrite.';

          //checking cached token
          if (!empty($this->auth_token)) {
            //cached
            $this->client->setAccessToken($this->auth_token);
            $this->last_messages[] = 'Auth token received from external cache.';
          }
          else {
            $this->last_messages[] = 'External cache did not provide an auth token.';
          }
        }

        //setup client
        $this->client->setApplicationName("PSL Sheets");
      }
      //re-check token
      if ($this->client->getAuth()->isAccessTokenExpired()) {
        //no token / cache token were rejected
        $this->last_messages[] = 'Auth token has expired.';
        //assertion
        $key = file_get_contents($this->p12_file_uri);
        $cred = new \Google_Auth_AssertionCredentials(
            $this->service_account_name,
            array('https://spreadsheets.google.com/feeds'),
            $key
        );
        $this->client->setAssertionCredentials($cred);

        $this->client->getAuth()->refreshTokenWithAssertion($cred);
        $this->auth_token = $this->client->getAccessToken();
      }
      else {
        $this->last_messages[] = 'Auth token OK.';
      }
      $token = json_decode($this->auth_token);
      $accessToken = $token->access_token;
      $serviceRequest = new DefaultServiceRequest($accessToken);
      ServiceRequestFactory::setInstance($serviceRequest);
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

    static $sheet_array;
    if ((isset($sheet_array)) && (!$reset)) {
      return $sheet_array;
    }

    $spreadsheets = $service->getSpreadsheets();

    $sheet_array = array();
    foreach ($spreadsheets as $sheet) {
      $id = $sheet->getId();
      $id = pathinfo($id);
      $sheet_array[$id['filename']] = $sheet->getTitle();
    }
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

    static $spreadsheet_static;

    if (!$service = $this->getGoogleService()) {
      // no need for an error here. It's handled in the service function
      return FALSE;
    }
    
    // @TODO: cache the ID of the sheet

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

    if (empty($spreadsheet_static)) {
      $spreadsheet_static = array();
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
    static $worksheet;

    if ((isset($worksheet)) && (!$reset)) {
      return $worksheet;
    }

    if (is_string($spreadsheet)) {
      $spreadsheet = $this->getSpreadsheet($spreadsheet, $reset);
    }

    $worksheetFeed = $spreadsheet->getWorksheets();
    $worksheet = $worksheetFeed->getByTitle($worksheet_name);

    return $worksheet;
  }

  /**
   * Cache the worksheet ID
   *
   * @param string  $spreadsheet_name The name of the worksheet
   * @param string  $worksheet_name   The worksheet Name
   * @param boolean $rest             Reset the cache
   *
   * @return string
   *  The ID of the worksheet
   */
  private function worksheetIdCache($spreadsheet_name, $worksheet_name, $reset = FALSE)
  {
    static $id;
    if ((isset($id)) && (!$reset)) {
      return $id;
    }

    $key = implode('-', array($spreadsheet_name, $worksheet_name));
    //Google cache - default using file
    $id = $this->client->getCache()->get($key);
    if ((empty($id)) || ($reset)) {
      $worksheet = $this->getWorksheet($spreadsheet_name, $worksheet_name);
      $id = $worksheet->getWorksheetId();
      $this->client->getCache()->set($key, $id);
     }

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

    static $cellfeed_static;

    if (isset($cellfeed_static[$worksheet_id]) && !$reset) {
      return $cellfeed_static[$worksheet_id];
    }
    if (empty($cellfeed_static)) {
      $cellfeed_static = array();
    }
    $cellFeed = $service->getCellFeed($worksheet_id);
    $cellfeed_static[$worksheet_id] = $cellFeed;

    return $cellFeed;
  }

  /**
   * Get the cellfeed for a single spreadsheet.
   *
   * @param object $worksheet    - The worksheet object
   *
   * @return CellFeed - The cell feed for the worksheet
   *
   */
  public function getCellFeed($worksheet)
  {
    $cellFeed = $worksheet->getCellFeed();
    
    return $cellFeed;
  }

  /**
   * Batch send values to a worksheet.
   *
   * @param string $worksheet        - the worksheet object
   * @param array  $data             - The data to send to the spreadsheet. Example
   *                                 array('A1' => 'hello', 'A2' => 'world')
   */
  public function batchSetData($worksheet, $data)
  {
    $batch_request = new BatchRequest();
    
    $cell_feed = $this->getCellFeed($worksheet);

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
   * @param string  $worksheet        - The worksheet object
   * @param array   $cell_return      - The values to return
   *
   * @return array - The cell values requested
   */
  public function batchGetCells($worksheet, $cell_return)
  {
    $cell_feed = $this->getCellFeed($worksheet);
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
    // set up worksheet for getCellFeed()
    $spreadsheet = $this->getSpreadsheet($spreadsheet_name, TRUE);
    $worksheet = $this->getWorksheet($spreadsheet, $worksheet_name);
    
    $this->batchSetData($worksheet, $data);
    $results = $this->batchGetCells($worksheet, $cell_return);
    
    return $results;
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

  /**
   * Method to check update gDoc auth token.
   * @method validate_token_expiry
   *
   * @param  boolean $force
   *    Enforce token change, via cache clear
   *
   * @return boolean
   */
  public function validate_token_expiry() {
    //$auth->revokeToken; can't be use - Google return err 400

    $auth  = $this->client->getAuth();

    return (!$auth->isAccessTokenExpired());
  }
}
