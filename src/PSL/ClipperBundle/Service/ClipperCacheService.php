<?php
/**
 * PSL/ClipperBundle/Controller/ClipperCacheService.php
 *
 * Clipper Cache Service.
 *
 * @version 1.0
 * @date 2015-08-01
 *
 */

namespace PSL\ClipperBundle\Service;

use PSL\ClipperBundle\Entity\ClipperCache;
use PSL\ClipperBundle\Entity\Repository\ClipperCacheRepository;
use \DateTime;

class ClipperCacheService {
  /**
   * PARAMS
   */
  //service availability
  private $enabled    = TRUE;
  //cache live time; in hour
  private $cache_life = 1;

  private $clipper_cache_rep;
  private $em;

  //handling records
  private $datetime_read_format  = 'Y-m-d H:i:s';
  private $default_cache_time_hr = 1;
  private $current_record;

  /**
   * Constructor function
   *
   * @param array $params - parameters defined with PSLclipper extension; clipper_cache.config
   */
  public function __construct($params) {
    $params           = (int) $params;
    $this->enabled    = ($params >= 0);
    $this->cache_life = ($params >= 1 ? $params : 0);
    if ($this->enabled) {
      global $kernel;
      $this->container = $kernel->getContainer();
      $this->em = $this->container->get('doctrine')->getManager();
      $this->clipper_cache_rep = $this->em->getRepository('PSLClipperBundle:ClipperCache');
    }
  }

  /**
   * Helper method to convert \ClipperCache object into reable-associative array.
   * @method unpack
   *
   * @return array
   */
  private function unpack() {
    if ((empty($this->current_record)) || (!is_object($this->current_record))) {
      return FALSE;
    }
    //restructure
    $cache = array(
      'name'        => $this->current_record->getName(),
      'data'        => $this->current_record->getData(),
      'expiries'    => $this->current_record->getExpiries(),
      'has_expired' => (!$this->validate_active()),
    );

    //expose data
    try {
      $cache['data'] = unserialize($cache['data']);
    }
    catch (\Symfony\Component\Debug\Exception\ContextErrorException $e) {
      //could be the data is just a simple string
      $cache['data'] = $cache['data'];
      // or the serialize string has broken
      // might need an external lib/regex try to fix the truncated serialized?
      // eg <http://shauninman.com/archive/2008/01/08/recovering_truncated_php_serialized_arrays>
    }

    //defaulting
    if (empty($cache['data'])) {
      $cache['data'] = array();
    }

    //formatting
    $cache['expiries'] = $cache['expiries']->format($this->datetime_read_format);

    return $cache;
  }

  /**
   * Helper method to create new cache / update record.
   * @method pack
   *
   * @param  string $name
   *    Cache name string.
   *
   * @param  mixed|string|array|object $values
   *    Cache new data.
   *
   * @param  object|boolean $expired
   *    \DateTime format of expired time, or FALSE to applied as defined as $this->default_cache_time_hr.
   *
   * @return void
   */
  private function pack($name, $values, $expired = FALSE) {
    $this->current_record = $this->clipper_cache_rep->findOneBy(array('name' => $name));

    //sanitize data
    if (!is_string($values)) {
      $values = serialize($values);
    }
    if (empty($expired)) {
      $expired = new DateTime("+{$this->default_cache_time_hr} hours");
    }

    //storing data
    $f = 'merge';
    if (is_null($this->current_record)) {
      //new
      $this->current_record = new ClipperCache();
      $this->current_record->setName($name);
      $f = 'persist';
    }
    $this->current_record->setName($name);
    $this->current_record->setData($values);
    $this->current_record->setExpiries($expired);
    $this->em->$f($this->current_record);
    $this->em->flush();
    $this->em->clear();
  }

  /**
   * Helper method to identify if given cache-record has expired.
   * @method validate_active
   *
   * @return boolean
   */
  private function validate_active() {
    if ((empty($this->current_record)) || (!is_object($this->current_record))) {
      return FALSE;
    }
    $expiries = $this->current_record->getExpiries();
    $now = new DateTime();
    return ($now < $expiries);
  }

  /**
   * Method to return service status.
   * @method is_enabled
   *
   * @return boolean
   */
  public function is_enabled() {

    return (!empty($this->enabled));
  }

  /**
   * Method to return current Cache lifetime.
   * @method get_cache_time
   *
   * @return integer
   */
  public function get_cache_time() {

    return $this->cache_life;
  }

  /**
   * Method to get cache record count.
   * @method get_cache_count
   *
   * @param  boolean $active_only
   *    Flag to get an Active records only.
   *
   * @return integer
   */
  public function get_cache_count($active_only = FALSE) {

    return $this->clipper_cache_rep->count($active_only);
  }

  /**
   * Method to flush cache records.
   * @method flush
   *
   * @param  boolean $all
   *    Flag to flush all cache including to which still active.
   *
   * @return boolean|integer
   *    Number of records were removed
   *    If FALSE were returned, there seems to be an issue on lower level (database);
   */
  public function flush($all = FALSE) {

    return $this->clipper_cache_rep->flush($all);
  }

  /**
   * Method to find a cache record via `clipper_cache`.`name`.
   * @method get
   *
   * @param  string $name
   *    Cache name string.
   *
   * @param  boolean $unpack
   *    Flag TRUE to sanitize data into readable associative data.
   *    Flag FALSE to return in raw \ClipperCache object.
   *
   * @return array|object
   */
  public function get($name, $unpack = TRUE, $false_on_expired = FALSE) {
    if (!$this->is_enabled()) {
      return FALSE;
    }
    $this->current_record = $this->clipper_cache_rep->findOneBy(array('name' => $name));
    if (is_null($this->current_record)) {
      return FALSE;
    }
    if (($false_on_expired) && ($this->validate_active($this->current_record) == FALSE)) {
      return FALSE;
    }
    if ($unpack) {
      return $this->unpack();
    }
    return $this->current_record;
  }

  /**
   * Method to insert or update cache record based on cache name.
   * @method set
   *
   * @param  string $name
   *    Cache name string.
   *
   * @param  mixed|string|array|object $values
   *    Data in string (kept as is) / array which will be serialized into record table.
   *
   * @param  object|boolean $expired
   *    \DateTime format of expired time, or FALSE to applied as defined on service params.
   */
  public function set($name, $values = '', $expired = FALSE) {
    if (!$this->is_enabled()) {
      return FALSE;
    }
    if (empty($expired)) {
      $expired = new DateTime("+{$this->cache_life} hours");
    }
    $this->pack($name, $values, $expired);
    return $this->current_record;
  }

  /**
   * Method to remove cache string by name.
   * @method delete
   *
   * @param  string $name
   *
   * @return boolean
   */
  public function delete($name) {
    if (!$this->is_enabled()) {
      return FALSE;
    }
    $found = $this->get($name, FALSE);
    if (!empty($found)) {
      $this->clipper_cache_rep->remove($found);
      return (empty($this->get($name, FALSE)));
    }
    return FALSE;
  }
}
