<?php
/**
 * Translator Service
 * 
 */
 
namespace PSL\ClipperBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

use \stdClass as stdClass;
use \Exception as Exception;

/**
 * Survey Builder Service for Clipper
 */
class TranslatorService
{
  
  protected $container;
  
  /**
   * an associative array of the languages as key and the translations as value
   */
  protected $translations;
  
  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }

  /**
   * Set Translations
   * 
   * @param array $translations
   */
  public function setTranslations($translations)
  {
    $this->translations = $translations;
  }
  
  /**
   * Translate function
   */
  public function t($string, $language)
  {
    // Loads the list of translations according to a language
    $translation_list = $this->translations[$language];
    
    $string_translated = $string;
    // If the string is set, replace the string
    if (isset($translation_list[$string])) {
      $string_translated = $translation_list[$string];
    }
    
    return $string_translated;
  }
}
