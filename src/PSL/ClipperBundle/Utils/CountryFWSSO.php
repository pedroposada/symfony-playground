<?php
/**
 * PSL/ClipperBundle/Utils/CountryFWSSO.php
 *
 * Clipper CountryFWSSO Utilities.
 * 
 * This class will provide a Country FWSSO mapping for general purpose.
 *
 * ----- NOT TO BE CONFUSED WITH MDM MAPPING ----- 
 * 
 * The mapping is for User Country and Currency to country
 * These IDs are related to the user object for FW SSO only.
 * 
 * @uses  Controller/ClipperController initBrainTree()
 * @uses  Controller/ClipperController formatPrice()
 *
 * @version 1.0
 * @date 2015-10-28
 *
 */

namespace PSL\ClipperBundle\Utils;

class CountryFWSSO {

  public static $map = array(
    'countries' => array(
      433 => "Afghanistan",
      434 => "Aland Islands",
      435 => "Albania",
      436 => "Algeria",
      437 => "American Samoa",
      438 => "Andorra",
      439 => "Angola",
      440 => "Anguilla",
      441 => "Antarctica",
      442 => "Antigua and Barbuda",
      443 => "Argentina",
      444 => "Armenia",
      445 => "Aruba",
      446 => "Australia",
      447 => "Austria",
      448 => "Azerbaijan",
      449 => "Bahamas",
      450 => "Bahrain",
      451 => "Bangladesh",
      452 => "Barbados",
      453 => "Belarus",
      454 => "Belgium",
      455 => "Belize",
      456 => "Benin",
      457 => "Bermuda",
      458 => "Bhutan",
      459 => "Bolivia",
      460 => "Bosnia and Herzegovina",
      461 => "Botswana",
      462 => "Bouvet Island",
      463 => "Brazil",
      464 => "British Indian Ocean Territory",
      465 => "Brunei Darussalam",
      466 => "Bulgaria",
      467 => "Burkina Faso",
      468 => "Burundi",
      469 => "Cambodia",
      470 => "Cameroon",
      471 => "Canada",
      472 => "Cape Verde",
      473 => "Cayman Islands",
      474 => "Central African Republic",
      475 => "Chad",
      476 => "Chile",
      477 => "China",
      478 => "Christmas Island",
      479 => "Cocos (Keeling) Islands",
      480 => "Colombia",
      481 => "Comoros",
      482 => "Congo",
      483 => "Congo, The Democratic Republic Of The",
      484 => "Cook Islands",
      485 => "Costa Rica",
      486 => "Côte d'Ivoire",
      487 => "Croatia",
      488 => "Cuba",
      489 => "Cyprus",
      490 => "Czech Republic",
      491 => "Denmark",
      492 => "Djibouti",
      493 => "Dominica",
      494 => "Dominican Republic",
      495 => "Ecuador",
      496 => "Egypt",
      497 => "El Salvador",
      498 => "Equatorial Guinea",
      499 => "Eritrea",
      500 => "Estonia",
      501 => "Ethiopia",
      502 => "Falkland Islands",
      503 => "Faroe Islands",
      504 => "Fiji",
      505 => "Finland",
      506 => "France",
      507 => "French Guiana",
      508 => "French Polynesia",
      509 => "French Southern Territories",
      510 => "Gabon",
      511 => "Gambia",
      512 => "Georgia",
      513 => "Germany",
      514 => "Ghana",
      515 => "Gibraltar",
      516 => "Greece",
      517 => "Greenland",
      518 => "Grenada",
      519 => "Guadeloupe",
      520 => "Guam",
      521 => "Guatemala",
      522 => "Guernsey",
      523 => "Guinea",
      524 => "Guinea-Bissau",
      525 => "Guyana",
      526 => "Haiti",
      527 => "Heard and McDonald Islands",
      528 => "Honduras",
      529 => "Hong Kong",
      530 => "Hungary",
      531 => "Iceland",
      532 => "India",
      533 => "Indonesia",
      534 => "Iran, Islamic Republic Of",
      535 => "Iraq",
      536 => "Ireland",
      537 => "Isle Of Man",
      538 => "Israel",
      539 => "Italy",
      540 => "Jamaica",
      541 => "Japan",
      542 => "Jersey",
      543 => "Jordan",
      544 => "Kazakhstan",
      545 => "Kenya",
      546 => "Kiribati",
      547 => "Korea, Democratic People's Republic Of",
      548 => "Kuwait",
      549 => "Kyrgyzstan",
      550 => "Lao People's Democratic Republic",
      551 => "Latvia",
      552 => "Lebanon",
      553 => "Lesotho",
      554 => "Liberia",
      555 => "Libyan Arab Jamahiriya",
      556 => "Liechtenstein",
      557 => "Lithuania",
      558 => "Luxembourg",
      559 => "Macao",
      560 => "Macedonia, Republic of",
      561 => "Madagascar",
      562 => "Malawi",
      563 => "Malaysia",
      564 => "Maldives",
      565 => "Mali",
      566 => "Malta",
      567 => "Marshall Islands",
      568 => "Martinique",
      569 => "Mauritania",
      570 => "Mauritius",
      571 => "Mayotte",
      572 => "Mexico",
      573 => "Micronesia, Federated States Of",
      574 => "Moldova",
      575 => "Monaco",
      576 => "Mongolia",
      577 => "Montenegro",
      578 => "Montserrat",
      579 => "Morocco",
      580 => "Mozambique",
      581 => "Myanmar",
      582 => "Namibia",
      583 => "Nauru",
      584 => "Nepal",
      585 => "Netherlands",
      586 => "Netherlands Antilles",
      587 => "New Caledonia",
      588 => "New Zealand",
      589 => "Nicaragua",
      590 => "Niger",
      591 => "Nigeria",
      592 => "Niue",
      593 => "Norfolk Island",
      594 => "Northern Mariana Islands",
      595 => "Norway",
      596 => "Oman",
      597 => "Pakistan",
      598 => "Palau",
      599 => "Panama",
      600 => "Papua New Guinea",
      601 => "Paraguay",
      602 => "Peru",
      603 => "Philippines",
      604 => "Pitcairn",
      605 => "Poland",
      606 => "Portugal",
      607 => "Puerto Rico",
      608 => "Qatar",
      609 => "Réunion",
      610 => "Romania",
      611 => "Russian Federation",
      612 => "Rwanda",
      613 => "Saint Barthelemy",
      614 => "Saint Helena",
      615 => "Saint Kitts and Nevis",
      616 => "Saint Lucia",
      617 => "Saint Martin",
      618 => "Saint Vincent and The Grenadines",
      619 => "Samoa",
      620 => "San Marino",
      621 => "Sao Tome and Principe",
      622 => "Saudi Arabia",
      623 => "Senegal",
      624 => "Serbia",
      625 => "Seychelles",
      626 => "Sierra Leone",
      627 => "Singapore",
      628 => "Slovakia",
      629 => "Slovenia",
      630 => "Solomon Islands",
      631 => "Somalia",
      632 => "South Africa",
      633 => "South Georgia and the South Sandwich Islands",
      634 => "South Korea",
      635 => "Spain",
      636 => "Sri Lanka",
      637 => "St. Pierre and Miquelon",
      638 => "Sudan",
      639 => "Suriname",
      640 => "Svalbard and Jan Mayen",
      641 => "Swaziland",
      642 => "Sweden",
      643 => "Switzerland",
      644 => "Syrian Arab Republic",
      645 => "Taiwan",
      646 => "Tajikistan",
      647 => "Tanzania, United Republic Of",
      648 => "Thailand",
      649 => "The Occupied Palestinian Territories",
      650 => "Timor-leste",
      651 => "Togo",
      652 => "Tokelau",
      653 => "Tonga",
      654 => "Trinidad and Tobago",
      655 => "Tunisia",
      656 => "Turkey",
      657 => "Turkmenistan",
      658 => "Turks and Caicos Islands",
      659 => "Tuvalu",
      660 => "Uganda",
      661 => "Ukraine",
      662 => "United Arab Emirates",
      663 => "United Kingdom",
      664 => "United States",
      665 => "United States Minor Outlying Islands",
      666 => "Uruguay",
      667 => "Uzbekistan",
      668 => "Vanuatu",
      669 => "Vatican City State (Holy See)",
      670 => "Venezuela",
      671 => "Viet Nam",
      672 => "Virgin Islands, British",
      673 => "Virgin Islands, U.S.",
      674 => "Wallis And Futuna",
      675 => "Western Sahara",
      676 => "Yemen",
      677 => "Zambia",
      678 => "Zimbabwe",
    ),
    'currencies' => array(
      'GBP' => array(
        663, // UK
      ),
      'EUR' => array(
        506, // France
        513, // Germany
        539, // Italy
        635, // Spain
        // European Union countries. Country list taken from Wikipedia /wiki/Euro.
        // Country spelling taken from DG sites (DocPass).
        447, // Austria
        454, // Belgium
        466, // Bulgaria
        487, // Croatia
        489, // Cyprus
        490, // Czech Republic
        491, // Denmark
        500, // Estonia
        505, // Finland
        516, // Greece
        530, // Hungary
        536, // Ireland
        551, // Latvia
        557, // Lithuania
        558, // Luxembourg
        566, // Malta
        585, // Netherlands
        605, // Poland
        606, // Portugal
        610, // Romania
        628, // Slovakia
        629, // Slovenia
        642, // Sweden
      ), 
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
   * Return country name by country id.
   * @method getCountry
   * 
   * Return country name or FALSE if nothing found.
   *
   * @param  int $country_id
   *
   * @return string country name
   */
  public static function getCountry($country_id) {
    return isset(self::$map['countries'][$country_id]) ? self::$map['countries'][$country_id] : FALSE;
  }
  
  /**
   * Return an array of country currency.
   * @method getCurrencies
   * 
   * This method may return a pivot data of registered currency/country.
   *
   * @param  boolean|string $specific_country
   *
   * @return array
   */
  public static function getCurrencies($specific_country = FALSE) {
    $countries_list = array();
    array_walk(self::$map['currencies'], function($countries, $currency) use (&$countries_list, $specific_country) {
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