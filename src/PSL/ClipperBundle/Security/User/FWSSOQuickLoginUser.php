<?php

namespace PSL\ClipperBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class FWSSOQuickLoginUser implements UserInterface
{
    protected $username;
    protected $password;
    protected $roles;

    public function __construct($username, $password, array $roles)
    {
        $this->username = $username;
        $this->password = $password;
        $this->roles = $roles;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
    }

    public function equals(UserInterface $user)
    {
        if (!$user instanceof FWSSOQuickLoginUser) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
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
