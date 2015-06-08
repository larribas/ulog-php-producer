<?php

namespace ulog;

class Producer
{
    const APPEND_ENDPOINT = '/streams/append';

    /** @var Client */
    private $client;

    /** @var bool */
    private $isSynchronous;

    /** @var array */
    private $queue;

    /**
     * Instantiate an event producer from a ULog client
     *
     * @param Client $client The ULog client that will be used to issue the requests
     * @param array $options Extra options to the producer (currently, the following options are allowed: synchronous)
     */
    public function __construct(Client $client, $options=array())
    {
        $this->client = $client;

        // Process options
        $this->isSynchronous = array_key_exists('synchronous', $options);

        $this->queue = array();
    }

    /**
     * Produce a sequence of events and invoke the provided callback with the results
     *
     * @param array $events
     * @param callable $callback
     */
    public function produce(array $events, callable $callback)
    {
        $eventsToAppend = array_map(function(Event $e){ return $e->toArray(); }, $events);

        /** @var \GuzzleHttp\Promise\Promise $promise */
        $promise = $this->client->postAsync(self::APPEND_ENDPOINT, array('json' => array($eventsToAppend)));

        if ($this->isSynchronous) {
            $promise->wait();
        }

        $promise->then(
            function($response) use($callback) {
                call_user_func($callback, $this->unwrapResponse($response));
            },
            function(\GuzzleHttp\Exception\RequestException $exc) use($callback) {
                call_user_func($callback, $this->unwrapResponse($exc->getResponse()));
            }
        );
    }

    /**
     * Add an event to the deferred queue
     *
     * @param Event $event
     */
    public function queue(Event $event)
    {
        $this->queue[] = $event;
    }

    /**
     * Flush all events in the deferred queue and invoke the provided callback with the server's response
     *
     * @param callable $callback The function to be invoked with the client's results
     */
    public function produceQueue(callable $callback)
    {
        $this->produce($this->queue, $callback);
        $this->clearQueue();
    }

    /**
     * Clears the queue of deferred events
     */
    public function clearQueue()
    {
        $this->queue = array();
    }

    /**
     * Unwraps the raw ULog response and turn it into a useful PHP construct
     */
    private function unwrapResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        // Test if the failed response is due to a general error or the fact that some of the events could not be appended
        $body = $response->getBody()->getContents();

        $json = json_decode($body, true);
        if (json_last_error() !== 0) {
            return $json;
        }

        return array('error' => $body);
    }
}