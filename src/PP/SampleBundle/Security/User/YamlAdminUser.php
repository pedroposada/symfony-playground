<?php

namespace PP\SampleBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class YamlAdminUser implements UserInterface
{
    protected $user_id;
    protected $username;
    protected $email;
    protected $password;
    protected $salt;
    protected $roles;

    public function __construct($user_id, $username, $email, $password, $salt, array $roles)
    {
        $this->user_id = $user_id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function eraseCredentials()
    {
    }

    public function equals(UserInterface $user)
    {
        if (!$user instanceof YamlUser) {
            return false;
        }

        if ($this->user_id !== $user->getUserId()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) {
            return false;
        }

        return true;
    }
}
