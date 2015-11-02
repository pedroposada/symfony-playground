<?php

// src/PSL/ClipperBundle/Service/UserService.php

namespace PSL\ClipperBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use PSL\ClipperBundle\Security\User\FWSSOUser;
use \stdClass as stdClass;
use \Exception as Exception;

/**
 * User Service
 */
class UserService
{
  protected $container;

  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }

  /**
   * Get user by user id
   *
   * @param   integer   $uid  User ID
   */
  public function getUserById($uid)
  {
    // $this->user = $this->container->get('security.context')->getToken()->getUser();
    $user = $this->getUserObject($uid);
    return new FWSSOUser($user['uid'], $user['name'], $user['mail'], $user['pass'], '', $user['roles']);
  }

  /**
   * Returns the user object from the FW SSO
   *
   * @param: int $user_id - FW SSO user id
   */
  private function getUserObject($user_id) {
    // User info retrieval from the FW SSO
    $settings['fwsso_baseurl'] = $this->container->getParameter('fwsso_api.url');
    $settings['fwsso_app_token'] = $this->container->getParameter('fwsso_api.app_token');

    $fwsso_ws = $this->container->get('fw_sso_webservice');
    $fwsso_ws->configure($settings);
    $response = $fwsso_ws->getUser(array('uid' => $user_id));

    if ($response->isOk()) {
      $content = @json_decode($response->getContent(), TRUE);
      if (json_last_error() != JSON_ERROR_NONE) {
        throw new Exception('Get User object - JSON decode error: ' . json_last_error());
      }
      else {
        return $content;
      }
    }
    else {
      return FALSE;
    }
  }
}
