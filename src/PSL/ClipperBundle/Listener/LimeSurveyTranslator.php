<?php

namespace PSL\ClipperBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

use PSL\ClipperBundle\Event\LimeSurveyTranslationEvent;

class LimeSurveyTranslator
{
  protected $container;
  
  public function __construct(ContainerInterface $container)
  {
    // this is @service_container
    $this->container = $container;
  }
  
  /**
   * Call back function of the event
   */
  public function onTranslate(LimeSurveyTranslationEvent $event) 
  {
    // Event properties
    $languages = $event->getLanguages();
    $elements = $event->getElements();
    $type = $event->getType();
    
    // Translator Service
    $ts = $this->container->get('translator_service');
    $kernel = $this->container->get('kernel');
    
    $translations = array();
    foreach ($languages as $language) {
      $yml_path = $kernel->locateResource('@PSLClipperBundle/Resources/translations/limesurvey/' . $type . '/messages.' . $language . '.yml');
      if ($yml_path) {
        $translations[$language] = Yaml::parse($yml_path);
      }
    }
    
    $ts->setTranslations($translations);
    
    // @TODO: create recursive function for Questions, Subquestions, and Answers translation
    
    // questions
    $questions = array();
    foreach ($elements['questions'] as $key => $question) {
      $original = $question['question'];
      foreach ($languages as $language) {
        $question['question'] = $ts->t($original, $language);
        $question['language'] = $language;
        if (isset($question['tokens']) && !empty($question['tokens'])) {
          $question['question'] = $this->tokenReplace($question['question'], $question['tokens']);
        }
        $questions[] = $question;
      }
    }
    $elements['questions'] = $questions;
    
    // subquestions
    $subquestions = array();
    foreach ($elements['subquestions'] as $key => $subquestion) {
      $original = $subquestion['question'];
      foreach ($languages as $language) {
        $subquestion['question'] = $ts->t($original, $language);
        $subquestion['language'] = $language;
        if (isset($subquestion['tokens']) && !empty($subquestion['tokens'])) {
          $subquestion['question'] = $this->tokenReplace($subquestion['question'], $subquestion['tokens']);
        }
        $subquestions[] = $subquestion;
      }
    }
    $elements['subquestions'] = $subquestions;
    
    // Answers
    $answers = array();
    foreach ($elements['answers'] as $key => $answer) {
      $original = $answer['answer'];
      foreach ($languages as $language) {
        $answer['answer'] = $ts->t($original, $language); 
        $answer['language'] = $language;
        if (isset($answer['tokens']) && !empty($answer['tokens'])) {
          $answer['answer'] = $this->tokenReplace($answer['answer'], $answer['tokens']);
        }
        $answers[] = $answer;
      }
    }
    $elements['answers'] = $answers;
    
    // Groups
    // NO translation needed but languages need to be inserted
    $groups = array();
    foreach ($elements['groups'] as $key => $group) {
      foreach ($languages as $language) {
        $group['language'] = $language;
        $groups[] = $group;
      }
    }
    $elements['groups'] = $groups;
    
    // question_attributes  
    // NO translation needed but languages need to be inserted
    $question_attributes = array();
    foreach ($elements['question_attributes'] as $key => $question_attribute) {
      // only add language if language is specified
      if (isset($question_attribute['language']) && !empty($question_attribute['language'])) {
        foreach ($languages as $language) {
          $question_attribute['language'] = $language;
          $question_attributes[] = $question_attribute;
        }
      } else {
        $question_attributes[] = $question_attribute;
      }
    }
    $elements['question_attributes'] = $question_attributes;
    
    // set all elements back into the event
    $event->setElements($elements);
  }

  /**
   * Token replacement function 
   */
  private function tokenReplace($string, $tokens) 
  {
    $string = strtr($string, $tokens);
    return $string;
  }
}
