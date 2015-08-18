<?php

namespace PSL\ClipperBundle\Service;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DrupalPasswordEncoder implements PasswordEncoderInterface
{
  
  private $container;

  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }
  
  public function encodePassword($raw, $salt)
  {
    $fwsso_config = $this->container->getParameter('fwsso_api');
    $settings['fwsso_baseurl'] = $fwsso_config['url'];
    $settings['fwsso_app_token'] = $fwsso_config['app_token'];
    
    $fwsso_ws = $this->container->get('fw_sso_webservice');
    $fwsso_ws->configure($settings);
    $param_arr['raw'] = $raw;
    $param_arr['salt'] = $salt;
    
    $response = $fwsso_ws->getHash($param_arr);

    if ($response->isOk()) {
      $content = @json_decode($response->getContent(), TRUE);
      if (json_last_error() != JSON_ERROR_NONE) {
        throw new Exception('JSON decode error: ' . json_last_error());
      }
      $hash = $content[0];
    } else {
      throw new Exception('Error creating user. ' . $response->getReasonPhrase());
    }
    
    return $hash;
  }

  public function isPasswordValid($encoded, $raw, $salt)
  {
    $password = $this->encodePassword($raw, $salt);
    return $encoded === $password;
  }

}