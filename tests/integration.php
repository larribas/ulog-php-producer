<?php

namespace ulog;

require '../vendor/autoload.php';
require __DIR__ . '/../Client.php';
require __DIR__ . '/../Event.php';
require __DIR__ . '/../Producer.php';

/*
 * This test is a straightforward way to test that the library is capable of producing many events successfully to
 * a ulog instance. It assumes there is a running ULog instance in 'http://192.168.59.103:7281'. If the event validator
 * has been populated, it will append all events and invoke the 'succ' callback. Otherwise, it will not append any,
 * and it will invoke 'fail'
 */

$client = new Client('http://192.168.59.103:7281', '');
$producer = new Producer($client, array('synchronous'));

$event = new Event('some-stream', 'some-type', 0 /* version */, 'partition key', time() * 1000000000 /* timestamp nanosecond precision */, 'content');

for($i = 0; $i < 100; $i++) {
    $producer->queue($event);
}

$succ = function($results) {
    error_log('successful!');
    error_log(var_export($results, true));
};

$fail = function($results) {
    error_log('failed');
    error_log(var_export($results, true));
};

$producer->produceQueue($succ, $fail);