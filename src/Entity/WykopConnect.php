<?php

namespace IDCT\Wykop\Entity;

use InvalidArgumentException;

class WykopConnect
{
    /**
     * User's login (username)
     *
     * @param string
     */
    protected $login;

    /**
     * User's token (connection key with the app)
     *
     * @param string
     */
    protected $token;

    /**
     * Signature of the message returned with this Wykop Connect data
     *
     * @param string
     */
    protected $signature;

    /**
     * App's used for connection key.
     *
     * @param string
     */
    protected $key;

    /**
     * Creates an instance of WykopConnect's response.
     *
     * @param string $login user's username.
     * @param string $token connectionKey (check SimpleWykop)
     * @param string $signature signature of the response message (md5 hash)
     * @param string $key login key (used for auth)
     */
    public function __construct(string $login, string $token, string $signature, string $key)
    {
        if (empty($login)) {
            throw new InvalidArgumentException("Missing login.");
        }

        $this->login = $login;

        if (empty($token)) {
            throw new InvalidArgumentException("Missing token.");
        }
        $this->token = $token;

        if (empty($signature)) {
            throw new InvalidArgumentException("Missing signature.");
        }
        $this->signature = $signature;

        if (empty($key)) {
            throw new InvalidArgumentException("Missing key.");
        }
        $this->key = $key;
    }

    /**
     * Returns the assigned previously user's login (username).
     *
     * @return string
     */
    public function getLogin() : string
    {
        return $this->login;
    }

    /**
     * Returns the assigned previously user connection token (connection key).
     *
     * @return string
     */
    public function getToken() : string
    {
        return $this->token;
    }

    /**
     * Returns the assigne signature.
     *
     * @return string
     */
    public function getSignature() : string
    {
        return $this->signature;
    }

    /**
     * Returns the assigned App's key.
     *
     * @return string
     */
    public function getKey() : string
    {
        return $this->key;
    }
}
