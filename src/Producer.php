<?php

namespace ulog;

/**
 * Class Producer
 *
 * Encapsulates the operations and parameters that will be used to produce events to a ULog instance
 *
 * @package ulog
 */
class Producer
{
    const APPEND_ENDPOINT = '/streams/events';

    /** @var Client */
    private $client;

    /** @var array */
    private $queue;

    // OPTIONS

    /** @var bool */
    private $isSynchronous;

    /** @var bool */
    private $forceAll;


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
        $this->isSynchronous = in_array('synchronous', $options);
        $this->forceAll = in_array('force_all', $options);

        $this->queue = array();
    }

    /**
     * Produce a sequence of events and invoke the provided callback with the results
     *
     * @param array $events
     * @param callable $success The function to be invoked with the client's results, if the request is successful
     * @param callable $failure The function to be invoked with the client's results, if the request is unsuccessful
     */
    public function produce(array $events, callable $success, callable $failure)
    {
        $eventsToAppend = array_map(function(Event $e){ return $e->toArray(); }, $events);

        /** @var \GuzzleHttp\Promise\Promise $promise */
        $promise = $this->client->postAsync(self::APPEND_ENDPOINT, array('json' => $eventsToAppend));

        $promise->then(
            function(\Psr\Http\Message\ResponseInterface $response) use($success) {
                call_user_func($success, json_decode($response->getBody()->getContents(), true));
            },
            function(\GuzzleHttp\Exception\RequestException $exc) use($failure) {
                call_user_func($failure, json_decode($exc->getResponse()->getBody()->getContents(), true));
            }
        );

        if ($this->isSynchronous) {
            $promise->wait();
        }
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
     * @param callable $success The function to be invoked with the client's results, if the request is successful
     * @param callable $failure The function to be invoked with the client's results, if the request is unsuccessful
     */
    public function produceQueue(callable $success, callable $failure)
    {
        $this->produce($this->queue, $success, $failure);
        $this->clearQueue();
    }

    /**
     * Clears the queue of deferred events
     */
    public function clearQueue()
    {
        $this->queue = array();
    }
}