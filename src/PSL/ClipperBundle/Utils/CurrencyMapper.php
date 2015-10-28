<?php
/**
 * PSL/ClipperBundle/Utils/CurrencyMapper.php
 *
 * Clipper CurrencyMapper Utilities.
 * 
 * This class will provide a Currency mapping for general purpose.
 * 
 * @uses  Controller/ClipperController initBrainTree()
 * @uses  Controller/ClipperController formatPrice()
 *
 * @version 1.0
 * @date 2015-10-28
 *
 */

namespace PSL\ClipperBundle\Utils;

class CurrencyMapper {

  public static $map = array(
    'GBP' => array(
      235, // UK
    ),
    'EUR' => array(
      77, // France
      84, // Germany
      110, // Italy
      210, // Spain,
      // European Union countries. Country list taken from Wikipedia /wiki/Euro.
      // Country spelling taken from DG sites (DocPass).
      15, // Austria
      22, // Belgium
      36, // Bulgaria
      57, // Croatia
      60, // Cyprus
      61, // Czech Republic
      62, // Denmark
      71, // Estonia
      76, // Finland
      87, // Greece
      101, // Hungary
      107, // Ireland
      122, // Latvia
      128, // Lithuania
      129, // Luxembourg
      137, // Malta
      156, // Netherlands
      178, // Poland
      179, // Portugal
      183, // Romania
      203, // Slovakia
      204, // Slovenia
      216, // Sweden
    ),
  );
    
  /**
   * Return the whole mapped data.
   * @method getMap
   *
   * @return array
   */
  public static function getMap() {
    return self::$map;
  }
  
  
  /**
   * Return list of country of a currency.
   * @method getCountries
   * 
   * This method may return an empty array, if no currency registered.
   *
   * @param  string $currency
   *
   * @return array
   */
  public static function getCountries($currency) {
    return isset(self::$map[$currency]) ? self::$map[$currency] : array();
  }
  
  /**
   * Return an array of country currency.
   * @method findCurrencies
   * 
   * This method may return a pivot data of registered currency/country.
   *
   * @param  boolean|string $specific_country
   *
   * @return array
   */
  public static function findCurrencies($specific_country = FALSE) {
    $countries_list = array();
    array_walk(self::$map, function($countries, $currency) use (&$countries_list, $specific_country) {
      array_walk($countries, function($country, $index) use (&$countries_list, $specific_country, $currency) {
        if ((!empty($specific_country)) && ($specific_country != $country)) {
          return;
        }
        if (isset($countries_list[$country])) {
          $countries_list[$country] = array();
        }
        $countries_list[$country][] = $currency;
      });
    });
    if (!empty($specific_country)) {
      return (isset($countries_list[$specific_country]) ? $countries_list[$specific_country] : FALSE);
    }
    asort($countries_list);
    return $countries_list;
  }
}