<?php

namespace PSL\ClipperBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class FWSSOQuickLoginUserProvider implements UserProviderInterface
{
  
  protected $container;
  
  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }

  public function loadUserByUsername($username)
  {
    
    $settings['fwsso_baseurl'] = $this->container->getParameter('fwsso_baseurl');
    
    // @TODO: modification on the FWSSO server side is required
    $fwsso_ws = $this->container->get('fw_sso_webservice');
    $fwsso_ws->configure($settings);
    $response = $fwsso_ws->quickLoginUser(array('username'=>$username)); // $username = docpass_hash
    $response = TRUE;
    
    if ($response) {
      
      // Username and password will be retrieved from the FW SSO
      $username = $response['username'];
      $password = 'password';
      $roles = array('ROLE_USER');
      
      $fwsso_user = new FWSSOQuickLoginUser($username, $password, $roles);
      
      return $fwsso_user;
    }
    
    // Return error if no user with this username 
    throw new UsernameNotFoundException(
      sprintf('Username "%s" does not exist.', $username)
    );
  }

  public function refreshUser(UserInterface $user)
  {
    if (!$user instanceof FWSSOQuickLoginUser) {
      throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
    }

    return $this->loadUserByUsername($user->getUsername());
  }

  public function supportsClass($class)
  {
    return $class === 'PSL\ClipperBundle\Security\User\FWSSOQuickLoginUser';
  }
}
