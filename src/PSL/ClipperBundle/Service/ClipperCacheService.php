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
  private $enabled    = FALSE;
  //cache live time; in hour
  private $cache_life = 1;

  private $clipper_cache_rep;
  private $em;

  /**
   * Constructor function
   *
   * @param array $params - the array of parameters for the Clipper Cache
   */
  public function __construct($params) {
    $this->enabled    = (!empty($params['enable']));
    $this->cache_life = (isset($params['cache_life']) ? $params['cache_life'] : 1);
    if ($this->enabled) {
      global $kernel;
      $this->container = $kernel->getContainer();
      $this->em = $this->container->get('doctrine')->getManager();
      $this->clipper_cache_rep = $this->em->getRepository('PSLClipperBundle:ClipperCache');
    }
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
    $find = $this->clipper_cache_rep->findOneBy(array('name' => $name));
    if (is_null($find)) {
      return FALSE;
    }
    if (($false_on_expired) && ($this->clipper_cache_rep->validate_active($find) == FALSE)) {
      return FALSE;
    }
    if ($unpack) {
      return $this->clipper_cache_rep->unpack($find);
    }
    return $find;
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
    if (empty($expired)) {
      $expired = new DateTime("+{$this->cache_life} hours");
    }
    return $this->clipper_cache_rep->pack($name, $values, $expired);
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
    $found = $this->get($name, FALSE);
    if (!empty($found)) {
      $this->clipper_cache_rep->remove($found);
      return (empty($this->get($name, FALSE)));
    }
    return FALSE;
  }
}