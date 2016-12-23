<?php

namespace PP\SampleBundle\Security\User;

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

        foreach ((array)$userDefinitions as $username => $attributes) {
            $user_id = isset($attributes['user_id']) ? $attributes['user_id'] : null;
            $email = isset($attributes['email']) ? $attributes['email'] : null;
            $password = isset($attributes['password']) ? $attributes['password'] : null;
            $salt = isset($attributes['salt']) ? $attributes['salt'] : null;
            $roles = isset($attributes['roles']) ? $attributes['roles'] : array();

            $this->users[$username] = new YamlAdminUser($user_id, $username, $email, $password, $salt, $roles);
        }
    }

    public function loadUserByUsername($username)
    {
        if (!isset($this->users[$username])) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        $user = $this->users[$username];

        return new YamlAdminUser($user->getUserId(), $user->getUsername(), $user->getEmail(), $user->getPassword(), $user->getSalt(), $user->getRoles());
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
        return $class === 'PP\SampleBundle\Security\User\YamlAdminUser';
    }
}
