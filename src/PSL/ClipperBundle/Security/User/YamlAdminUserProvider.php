<?php

namespace PSL\ClipperBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use Symfony\Component\Yaml\Yaml;

class YamlAdminUserProvider implements UserProviderInterface
{
    protected $users;

    public function __construct($yml_path)
    {
        $userDefinitions = Yaml::parse($yml_path);

        $this->users = array();

        foreach ($userDefinitions as $username => $attributes) {
            $password = isset($attributes['password']) ? $attributes['password'] : null;
            $roles = isset($attributes['roles']) ? $attributes['roles'] : array();

            $this->users[$username] = new YamlAdminUser($username, $password, $roles);
        }
    }

    public function loadUserByUsername($username)
    {
        if (!isset($this->users[$username])) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        $user = $this->users[$username];

        return new YamlAdminUser($user->getUsername(), $user->getPassword(), $user->getRoles());
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof YamlAdminUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'PSL\ClipperBundle\Security\User\YamlAdminUser';
    }
}
