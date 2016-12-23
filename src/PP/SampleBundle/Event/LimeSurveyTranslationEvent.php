<?php
/**
 * LimeSurvey translation Event
 * 
 * Event to transport data related to lime survey translation
 */

namespace PP\SampleBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class LimeSurveyTranslationEvent extends Event
{
  
  protected $elements;
  protected $languages;
  protected $type;

  public function __construct(Array $elements, Array $languages, $type)
  {
    $this->elements = $elements;
    $this->languages = $languages;
    $this->type = $type;
  }

  /**
   * Set Elements
   * 
   * @param array $elements
   */
  public function setElements($elements)
  {
    $this->elements = $elements;
  }
  
  /**
   * Get Elements
   *
   * @return array 
   */
  public function getElements()
  {
    return $this->elements;
  }
  
  /**
   * Get Languages
   * 
   * @return array
   */
  public function getLanguages()
  {
    return $this->languages;
  }
  
  /**
   * Get Type
   * 
   * @param array $type
   */
  public function getType()
  {
    return $this->type;
  }
}
