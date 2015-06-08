<?php

namespace ulog;

/**
 * Class Event
 *
 * DTO That represents a ULog event prior to being produced
 *
 * @package ulog
 */
class Event
{
    /** @var string */
    public $streamName;

    /** @var string */
    public $type;

    /** @var int */
    public $version;

    /** @var string */
    public $partitionKey;

    /** @var int */
    public $time;

    /** @var mixed */
    public $content;

    /**
     * Instantiate a new Event
     *
     * @param string $streamName
     * @param string $type
     * @param int $version
     * @param string $partitionKey
     * @param int $time
     * @param mixed $content
     */
    public function __construct($streamName, $type, $version, $partitionKey, $time, $content)
    {
        $this->streamName = $streamName;
        $this->type = $type;
        $this->version = $version;
        $this->partitionKey = $partitionKey;
        $this->time = $time;
        $this->content = $content;
    }

    /**
     * Translates the event to an array formatted with JSON naming conventions (the format used by ULog's API)
     *
     * @return array representing the event format ULog understands
     */
    public function toArray()
    {
        return array(
            'stream' => $this->streamName,
            'type' => $this->type,
            'version' => $this->version,
            'partition_key' => $this->partitionKey,
            'time' => $this->time,
            'content' => $this->content,
        );
    }
}