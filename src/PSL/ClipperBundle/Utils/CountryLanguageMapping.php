<?php
/**
 * PSL/ClipperBundle/Utils/CountryLanguageMapping.php
 *
 * Clipper CountryLanguageMapping Utilities.
 * 
 * Returns a language according to the country
 *
 * @version 1.0
 * @date 2015-10-28
 *
 */

namespace PSL\ClipperBundle\Utils;

class CountryLanguageMapping {

  public static $map = array(
    "countries" => array(
      "France" => "fr",
      "Germany" => "de",
      "Italy" => "it",
      "UK" => "en",
      "USA" => "en",
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
   * Return languge ISO code name by country (market).
   * @method getLanguage
   * 
   * Return country name or FALSE if nothing found.
   *
   * @param  int $market
   *
   * @return string language
   */
  public static function getLanguage($market) {
    //default to en
    return isset(self::$map["countries"][$market]) ? self::$map["countries"][$market] : "en";
  }
  
}