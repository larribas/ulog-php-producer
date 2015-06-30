# ULog PHP Producer

This is a PHP library for [ULog](https://github.com/socialpoint/ulog) that focuses on producing events and sending them to ULog.

This project's goal is to provide a straightforward interface so that PHP applications can easily append events to a ULog enterprise log


## Usage Examples

### Produce events immediately

```php
// Clients are supplied the ULog host:port combination, and an access token
$client = new ulog\Client('http://localhost', 'Acngqr8chd');
$producer = new ulog\Producer($client);

$events = array(
    new ulog\Event('stream name', 'event type', 2 /* version */, 'partition key', 214235355 /* timestamp */, $content)
);

$producer->produce($events, $callback);
```


### Queue events to defer its production up to a certain moment

```php
// Clients are supplied the ULog host:port combination, and an access token
$client = new ulog\Client('http://localhost', 'Acngqr8chd');
$producer = new ulog\Producer($client);

$event = new ulog\Event('stream name', 'event type', 2 /* version */, 'partition key', 214235355 /* timestamp */, $content);

// n times
$producer->queue($event);

$producer->produceQueue($callback);
```


### Produce events synchronously

```php
// Clients are supplied the ULog host:port combination, and an access token
$client = new ulog\Client('http://localhost', 'Acngqr8chd');
$producer = new ulog\Producer($client, array('synchronous'));

$events = array(
    new ulog\Event('stream name', 'event type', 2 /* version */, 'partition key', 214235355 /* timestamp */, $content)
);

$producer->produce($events, $callback);
```


## Callbacks

Callbacks will be invoked with an array as its only argument. The array will contain one of these structures:

```php
[
    [
        [
            'appended' => true,
            'stream' => 'existent',
            'partition' => 3,
            'error' => ''
        ],
        [
            'appended' => false,
            'stream' => 'nonexistent',
            'partition' => 0,
            'error' => 'The specified stream does not exist'
        ]
    ]
]
```

or

```php
[
    'error_type' => 'ErrorType',
    'error_message' => 'Error message returned by the server, clarifying the error'
]
```
