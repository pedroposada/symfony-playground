<?php

// src/PP/SampleBundle/Service/UserService.php

namespace PP\SampleBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * Get current user.
   */
  public function getCurrent()
  {
    return $this->container->get('security.context')->getToken()->getUser();
  }

  /**
   * Find an user by user ID.
   *
   * @param   integer   $user_id  User ID
   *
   * @return  array
   */
  public function findById($user_id)
  {
    // User info retrieval from the FW SSO
    $settings['fwsso_baseurl'] = $this->container->getParameter('fwsso_api.url');
    $settings['fwsso_app_token'] = $this->container->getParameter('fwsso_api.app_token');

    $fwsso_ws = $this->container->get('fw_sso_webservice');
    $fwsso_ws->configure($settings);
    $response = $fwsso_ws->getUser(array('uid' => $user_id));

    if ($response->isOk()) {
      $content = @json_decode($response->getContent(), TRUE);
      if (json_last_error() != JSON_ERROR_NONE) {
        return NULL;
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
