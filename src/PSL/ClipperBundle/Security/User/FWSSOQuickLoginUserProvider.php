<?php

namespace PSL\ClipperBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

class FWSSOQuickLoginUserProvider implements UserProviderInterface
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
    
    // @TODO: modification on the FWSSO server side is required
    $fwsso_ws = $this->container->get('fw_sso_webservice');
    $fwsso_ws->configure($settings);
    $response = $fwsso_ws->quickLoginUser(array('qlhash'=>$username)); // $username = docpass_hash

    if ($response->isOk()) {
      // Username and password retrieval from the FW SSO
      $content = @json_decode($response->getContent(), TRUE);
      if (json_last_error() != JSON_ERROR_NONE) {
        throw new Exception('JSON decode error: ' . json_last_error());
      }
      $username = $content['account']['name'];
      $password = 'password';

      $roles = $this->getRoles($username);
      
      $fwsso_user = new FWSSOQuickLoginUser($username, $password, $roles);

      return $fwsso_user;
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
