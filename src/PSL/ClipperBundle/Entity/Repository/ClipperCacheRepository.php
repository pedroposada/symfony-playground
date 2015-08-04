<?php

namespace PSL\ClipperBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\Criteria;

use PSL\ClipperBundle\Entity\ClipperCache;
use \DateTime;

/**
 * ClipperCacheRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ClipperCacheRepository extends EntityRepository
{
  private $datetime_read_format  = 'Y-m-d H:i:s';
  private $datetime_db_format    = 'Y-m-d H:i:s';
  private $default_cache_time_hr = 1;

  /**
   * Helper method to convert \ClipperCache object into reable-associative array.
   * @method unpack
   *
   * @param  object $cache_record
   *    \ClipperCache object.
   *
   * @return array
   */
  public function unpack($cache_record) {
    if ((empty($cache_record)) || (!is_object($cache_record))) {
      return $cache_record;
    }
    //restructure
    $active = $this->validate_active($cache_record);
    $cache = array(
      'name'        => $cache_record->getName(),
      'data'        => $cache_record->getData(),
      'expiries'    => $cache_record->getExpiries(),
      'has_expired' => ($active == FALSE),
    );

    //expose data
    try {
      $cache['data'] = unserialize($cache['data']);
    }
    catch (\Symfony\Component\Debug\Exception\ContextErrorException $e) {
      //could be the data is just a simple string
      $cache['data'] = $cache['data'];
      // or the serialize string has broken
      // might need an external lib/regx try to fix the truncated serialized?
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
   * @return \ClipperCache object
   */
  public function pack($name, $values, $expired = FALSE) {
    $em    = $this->getEntityManager();
    $rep   = $em->getRepository('PSLClipperBundle:ClipperCache');
    $found = $rep->findOneBy(array('name' => $name));

    //sanitize data
    if (!is_string($values)) {
      $values = serialize($values);
    }
    if (empty($expired)) {
      $expired = new DateTime("+{$this->default_cache_time_hr} hours");
    }

    //storing data
    $f = 'merge';
    if (is_null($found)) {
      $found = new ClipperCache();
      $found->setName($name);
      $f = 'persist';
      //new
    }
    $found->setName($name);
    $found->setData($values);
    $found->setExpiries($expired);
    $em->$f($found);
    $em->flush();
    $em->clear();
    return $found;
  }

  /**
   * Helper method to count cache records.
   * @method count
   *
   * @param  boolean $active_only
   *    Flag to count all / to which still active.
   *
   * @return integer
   *    Found record count.
   */
  public function count($active_only = FALSE) {
    $qb = $this->getEntityManager()->createQueryBuilder();
    $qb->select('COUNT(c.name)');
    $qb->from('PSLClipperBundle:ClipperCache','c');
    if ($active_only) {
      $now = new DateTime();
      $qb->where('c.expiries >= :now');
      $qb->setParameter('now', $now, \Doctrine\DBAL\Types\Type::DATETIME);
    }
    return (int) $qb->getQuery()->getSingleScalarResult();
  }

  /**
   * Helper method to flush cache records.
   * @method flush
   *
   * @param  boolean $all
   *    Flag to remove all cache records.
   *
   * @return integer
   *    Removed records count.
   */
  public function flush($all = FALSE) {
    $qb = $this->getEntityManager()->createQueryBuilder();
    $qb->delete('PSLClipperBundle:ClipperCache', 'c');
    if (!$all) {
      $now = new DateTime();
      $qb->where('c.expiries <= :now');
      $qb->setParameter('now', $now, \Doctrine\DBAL\Types\Type::DATETIME);
    }
    return $qb->getQuery()->execute();
  }

  /**
   * Helper method to remove cache record by name.
   * @method remove
   *
   * @param  object $cache_record
   *    \ClipperCache object.
   *
   * @return boolean
   *    Remove complete.
   */
  public function remove($cache_record) {
    if ((empty($cache_record)) || (!is_object($cache_record))) {
      return FALSE;
    }
    $em = $this->getEntityManager();
    $em->remove($cache_record);
    $em->flush();
    return TRUE;
  }

  public function validate_active($cache_record) {
    if ((empty($cache_record)) || (!is_object($cache_record))) {
      return FALSE;
    }
    $expiries = $cache_record->getExpiries();
    $now = new DateTime();
    return ($now < $expiries);
  }
}