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
    
    $settings['fwsso_baseurl'] = $this->container->getParameter('fwsso_baseurl');
    
    $fwsso_ws = $this->container->get('fw_sso_webservice');
    $fwsso_ws->configure($settings);
    $param_arr['raw'] = $raw;
    $hash = $fwsso_ws->getHash($param_arr);
    
    return $hash;
  }

  public function isPasswordValid($encoded, $raw, $salt)
  {
    return $encoded === $this->encodePassword($raw, $salt);
  }

}