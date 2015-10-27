<?php

namespace PSL\ClipperBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

// use PSL\ClipperBundle\Service\FWSSOWebservice as FWSSOWebservice;

class FWSSOUserProvider implements UserProviderInterface
{
  
  protected $container;
  protected $invoice_whitelist;
  
  public function __construct(ContainerInterface $container, $invoice_whitelist)
  {
    $this->container = $container;
    $this->invoice_whitelist = $invoice_whitelist;
  }

  public function loadUserByUsername($username)
  {
    $settings['fwsso_baseurl'] = $this->container->getParameter('fwsso_api.url');
    $settings['fwsso_app_token'] = $this->container->getParameter('fwsso_api.app_token');
    
    $fwsso_ws = $this->container->get('fw_sso_webservice');
    $fwsso_ws->configure($settings);
    $response = $fwsso_ws->loginUser(array('username' => $username));
    
    if ($response->isOk()) {
      // Username and password retrieval from the FW SSO
      $content = @json_decode($response->getContent(), TRUE);
      if (json_last_error() != JSON_ERROR_NONE) {
        throw new Exception('JSON decode error: ' . json_last_error());
      }
      $userId = $content['uid'];
      $username = $content['name'];
      $password = $content['pass'];
      $salt = substr($content['pass'], 0, 12);

      $roles = $this->getRoles($username);
      
      return new FWSSOUser($userId, $username, $password, $salt, $roles);
    }
    
    // Return error if no user with this username 
    throw new UsernameNotFoundException(
      sprintf('Username "%s" does not exist.', $username)
    );
  }

  public function getRoles($username)
  {
    $yaml = new Parser();
    try {
      $invoice_wl = $yaml->parse(file_get_contents($this->invoice_whitelist));
    } catch (ParseException $e) {
      return array('ROLE_USER');
    }
    if (isset($invoice_wl[$username])) {
      return $invoice_wl[$username]['roles'];
    }
    return array('ROLE_USER');
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
