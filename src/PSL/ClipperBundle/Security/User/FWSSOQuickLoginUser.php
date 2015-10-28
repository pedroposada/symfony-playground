<?php

namespace PSL\ClipperBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class FWSSOQuickLoginUser implements UserInterface
{
    protected $userId;
    protected $username;
    protected $email;
    protected $password;
    protected $roles;

    public function __construct($userId, $username, $email, $password, array $roles)
    {
        $this->userId = $userId;
        $this->username = $username;
        $this->email = $email;
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

    public function getEmail()
    {
        return $this->email;
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

    public function getQuickLoginHash($key)
    {        
        $string_to_encrypt = array(
          'id' => trim($this->username),
        );
        $string_to_encrypt = json_encode($string_to_encrypt);

        $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string_to_encrypt, MCRYPT_MODE_CBC, md5(md5($key))));
        $encoded = strtr($encrypted, '+/=', '-_,');

        return $encoded;
    }
}
