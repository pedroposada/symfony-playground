<?php

namespace PSL\ClipperBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

// use PSL\ClipperBundle\Service\FWSSOWebservice as FWSSOWebservice;

class FWSSOUserProvider implements UserProviderInterface
{
  
  protected $container;
  
  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }

  public function loadUserByUsername($username)
  {
    $fwsso_config = $this->container->getParameter('fwsso_api');
    $settings['fwsso_baseurl'] = $fwsso_config['url'];
    $settings['fwsso_app_token'] = $fwsso_config['app_token'];
    
    $fwsso_ws = $this->container->get('fw_sso_webservice');
    $fwsso_ws->configure($settings);
    $response = $fwsso_ws->loginUser(array('username' => $username));
    
    if ($response->isOk()) {
      // Username and password retrieval from the FW SSO
      $content = @json_decode($response->getContent(), TRUE);
      if (json_last_error() != JSON_ERROR_NONE) {
        throw new Exception('JSON decode error: ' . json_last_error());
      }
      $username = $content['name'];
      $password = $content['pass'];
      $salt = substr($content['pass'], 0, 12);
      $roles = array('ROLE_USER');
      
      return new FWSSOUser($username, $password, $salt, $roles);
    }
    
    // Return error if no user with this username 
    throw new UsernameNotFoundException(
      sprintf('Username "%s" does not exist.', $username)
    );
  }

  public function refreshUser(UserInterface $user)
  {
    if (!$user instanceof FWSSOUser) {
      throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
    }

    return $this->loadUserByUsername($user->getUsername());
  }

  public function supportsClass($class)
  {
    return $class === 'PSL\ClipperBundle\Security\User\FWSSOUser';
  }
}
