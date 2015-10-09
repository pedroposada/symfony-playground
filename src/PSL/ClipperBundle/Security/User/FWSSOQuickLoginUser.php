<?php

namespace PSL\ClipperBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class FWSSOQuickLoginUser implements UserInterface
{
    protected $userId;
    protected $username;
    protected $password;
    protected $roles;

    public function __construct($userId, $username, $password, array $roles)
    {
        $this->userId = $userId;
        $this->username = $username;
        $this->password = $password;
        $this->roles = $roles;
    }

    public function getSalt()
    {
    }
    
    public function getUserId()
    {
        return $this->userId;
    }
    
    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
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
        if (!$user instanceof FWSSOQuickLoginUser) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->userId !== $user->getUserId()) {
            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) {
            return false;
        }

        return true;
    }
}
