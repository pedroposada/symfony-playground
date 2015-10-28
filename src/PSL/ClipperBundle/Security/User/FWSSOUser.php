<?php

namespace PSL\ClipperBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class FWSSOUser implements UserInterface
{
    protected $userId;
    protected $username;
    protected $email;
    protected $password;
    protected $salt;
    protected $roles;

    public function __construct($userId, $username, $email, $password, $salt, array $roles)
    {
        $this->userId = $userId;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
    }

    public function getUserId()
    {
        return $this->userId;
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
        if (!$user instanceof FWSSOUser) {
            return false;
        }

        if ($this->userId !== $user->getUserId()) {
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
