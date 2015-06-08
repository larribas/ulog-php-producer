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
     * @param string $host Host of the ULog instance
     * @param string $port Port of the ULog instance
     * @param string $token Access token to include in each request's Authorization header
     */
    public function __construct($host, $port, $token)
    {
        $this->guzzleClient = new \GuzzleHttp\Client(array(
            'base_uri' => sprintf('%s:%s', $host, $port),
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
     */
    public function __call($name, $arguments)
    {
        // Delegate missing methods to Guzzle's Client
        call_user_func_array(array($this->guzzleClient, $name), $arguments);
    }
}