<?php
/**
 * PSL/ClipperBundle/Utils/GeoMapperService.php
 *
 * Clipper GeoMapper Utilities.
 * 
 * This class will provide a Geography mapping for general purpose.
 * Some of the logic may generate duplicated contents such as UK & United Kingdom.
 * 
 * @uses  Charts/Assembler setChartEvent()
 * @uses  Charts/Assembler filterResponsesDrillDown()
 *
 * @version 1.0
 * @date 2015-09-14
 *
 */

namespace PSL\ClipperBundle\Utils;

class GeoMapper {  
  protected static $map;
  /**
   * Define map.
   * @method __construct
   * 
   * Structure
   * $map => 
   *   REGION-1 =>
   *     COUNTRY-A
   *     COUNTRY-B
   *     ...
   *   REGION-2 =>
   *     COUNTRY-C
   *     ...
   *   ...
   *
   * @param  ContainerInterface $container
   */
  public function __construct() {
    self::$map = array(
      'EU5' => array(
        'France',
        'Germany',
        'Italy',
        'Spain',
        'UK',
      ),
      //additional data from net
    /** 
    //disabled since this is not in use, TBA
      'Eastern Africa' => array(
        'Burundi',
        'Comoros',
        'Djibouti',
        'Eritrea',
        'Ethiopia',
        'Kenya',
        'Madagascar',
        'Malawi',
        'Mauritius',
        'Mayotte',
        'Mozambique',
        'Reunion',
        'Rwanda',
        'Seychelles',
        'Somalia',
        'United Republic of Tanzania',
        'Tanzania',
        'Uganda',
        'Zambia',
        'Zimbabwe',
      ),
      'Middle Africa' => array(
        'Angola',
        'Cameroon',
        'Central African Republic',
        'Chad',
        'Congo',
        'Democratic Republic of the Congo',
        'Equatorial Guinea',
        'Gabon',
        'Sao Tome and Principe',
      ),
      'Northern Africa' => array(
        'Algeria',
        'Egypt',
        'Libyan Arab Jamahiriya',
        'Morroco',
        'South Sudan',
        'Sudan',
        'Tunisia',
        'Western Sahara',
      ),
      'Southern Africa' => array(
        'Botswana',
        'Lesotho',
        'Namibia',
        'South Africa',
        'Swaziland',
      ),
      'Western Africa' => array(
        'Benin',
        'Burkina Faso',
        'Cape Verde',
        'Cote d\'Ivoire (Ivory Coast)',
        'Gambia',
        'Ghana',
        'Guinea',
        'Guinea-Bissau',
        'Liberia',
        'Mali',
        'Mauritania',
        'Niger',
        'Nigeria',
        'Saint Helena',
        'Senegal',
        'Sierra Leone',
        'Togo',
      ),
      'Caribbean' => array(
        'Anguilla',
        'Antigua and Barbuda',
        'Aruba',
        'Bahamas',
        'Barbados',
        'Bonaire, Saint Eustatius and Saba',
        'British Virgin Islands',
        'Cayman Islands',
        'Cuba',
        'Curaçao',
        'Dominica',
        'Dominican Republic',
        'Grenada',
        'Guadeloupe',
        'Haiti',
        'Jamaica',
        'Martinique',
        'Monserrat',
        'Puerto Rico',
        'Saint-Barthélemy',
        'St. Kitts and Nevis',
        'Saint Lucia',
        'Saint Martin',
        'Saint Vincent and the Grenadines',
        'Sint Maarten',
        'Trinidad and Tobago',
        'Turks and Caicos Islands',
        'Virgin Islands (US)',
      ),
      'Central America' => array(
        'Belize',
        'Costa Rica',
        'El Salvador',
        'Guatemala',
        'Honduras',
        'Mexico',
        'Nicaragua',
        'Panama',
      ),
      'South America' => array(
        'Argentina',
        'Bolivia',
        'Brazil',
        'Chile',
        'Colombia',
        'Ecuador',
        'Falkland Islands',
        'Malvinas',
        'French Guiana',
        'Guyana',
        'Paraguay',
        'Peru',
        'Suriname',
        'Uruguay',
        'Venezuela',
      ),
      'Northern America' => array(
        'Bermuda',
        'Canada',
        'Greenland',
        'Saint Pierre and Miquelon',
        'United States',
      ),
      'Asia' => array(
        'Afganistan',
        'Armenia',
        'Azerbaijan',
        'Bangladesh',
        'Bhutan',
        'Brunei Darussalam',
        'Cambodia',
        'China',
        'Georgia',
        'Hong Kong',
        'India',
        'Indonesia',
        'Japan',
        'Kazakhstan',
        'North Korea',
        'South Korea',
        'Kyrgyzstan',
        'Laos',
        'Macao',
        'Malaysia',
        'Maldives',
        'Mongolia',
        'Myanmar',
        'Nepal',
        'Pakistan',
        'Phillipines',
        'Singapore',
        'Sri Lanka',
        'Taiwan',
        'Tajikistan',
        'Thailand',
        'Timor Leste',
        'Turkmenistan',
        'Uzbekistan',
        'Vietnam',
      ),
      'Europe' => array(
        'Albania',
        'Andorra',
        'Belarus',
        'Bosnia',
        'Croatia',
        'European Union',
        'Faroe Islands',
        'Gibraltar',
        'Guerney and Alderney',
        'Iceland',
        'Jersey',
        'Kosovo',
        'Liechtenstein',
        'Macedonia',
        'Island of Man',
        'Moldova',
        'Monaco',
        'Montenegro',
        'Norway',
        'Russia',
        'San Marino',
        'Serbia',
        'Svalbard and Jan Mayen Islands',
        'Switzerland',
        'Turkey',
        'Ukraine',
        'Vatican City State',
      ),
      'European Union' => array(
        'Austria',
        'Belgium',
        'Bulgaria',
        'Cyprus',
        'Czech Republic',
        'Denmark',
        'Estonia',
        'Finland',
        'France',
        'Germany',
        'Greece',
        'Hungary',
        'Ireland',
        'Italy',
        'Latvia',
        'Lithuania',
        'Luxembourg',
        'Malta',
        'Netherlands',
        'Poland',
        'Portugal',
        'Romania',
        'Slovakia',
        'Slovenia',
        'Spain',
        'Sweden',
        'United Kingdom',
        'UK',
      ),
      'Middle East' => array(
        'Bahrain',
        'Iraq',
        'Iran',
        'Israel',
        'Jordan',
        'Kuwait',
        'Lebanon',
        'Oman',
        'Palestine',
        'Qatar',
        'Saudi Arabia',
        'Syria',
        'United Arab Emirates',
        'Yemen',
      ),
      'Oceania' => array(
        'Australia',
        'Fiji',
        'French Polynesia',
        'Guam',
        'Kiribati',
        'Marshall Islands',
        'Micronesia',
        'New Caledonia',
        'New Zealand',
        'Papua New Guinea',
        'Samoa',
        'American Samoa',
        'Solomon Islands',
        'Tonga',
        'Vanuatu',
      ),
    **/
    );
  }
    
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
   * Return all registered Regions.
   * @method getRegions
   *
   * @return array
   */
  public function getRegions() {
    return array_keys(self::$map); 
  }
  
  /**
   * Return list of country of a region.
   * @method getCountries
   * 
   * This method may return an empty array, if no region registered.
   *
   * @param  string $region
   *
   * @return array
   */
  public function getCountries($region) {
    if (isset(self::$map[$region])) {
      return self::$map[$region];
    }
    return array();
  }
  
  /**
   * Return an array of country region(s).
   * @method findRegions
   * 
   * This method may return a pivot data of registered region/country.
   *
   * @param  boolean|string $specific_country
   *
   * @return array
   */
  public function findRegions($specific_country = FALSE) {
    $countries_list = array();
    array_walk(self::$map, function($countries, $region) use (&$countries_list, $specific_country) {
      array_walk($countries, function($country, $index) use (&$countries_list, $specific_country, $region) {
        if ((!empty($specific_country)) && ($specific_country != $country)) {
          return;
        }
        if (isset($countries_list[$country])) {
          $countries_list[$country] = array();
        }
        $countries_list[$country][] = $region;
      });
    });
    if (!empty($specific_country)) {
      return (isset($countries_list[$specific_country]) ? $countries_list[$specific_country] : array());
    }
    asort($countries_list);
    return $countries_list;
  }
}