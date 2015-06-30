<?php

namespace ulog;

/**
 * Class Client
 *
 * Encapsulates the client configuration the producer will use to communicate with the ULog instance
 *
 * @package ulog
 */
class Client
{
    private $guzzleClient;

    /**
     * Instantiate a new client with the appropriate request format headers and base url
     *
     * @param string $address Address of the ULog instance
     * @param string $token Access token to include in each request's Authorization header
     */
    public function __construct($address, $token)
    {
        $this->guzzleClient = new \GuzzleHttp\Client(array(
            'base_uri' => $address,
            'headers' => array(
                'Authorization' => $token
            )
        ));
    }

    /**
     * Delegate requests issued to ULog to the inner guzzle client
     *
     * @param string $name Name of the invoked method
     * @param array $arguments List of arguments to such method
     * @return mixed The result of said function
     */
    public function __call($name, $arguments)
    {
        // Delegate missing methods to Guzzle's Client
        return call_user_func_array(array($this->guzzleClient, $name), $arguments);
    }
}