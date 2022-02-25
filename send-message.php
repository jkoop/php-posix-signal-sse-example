<?php

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit;
}

/*****/

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required(['REDIS_HOST', 'REDIS_PORT', 'REDIS_PREFIX']);

$redis = new Redis; // this requires php-redis (https://github.com/phpredis/phpredis)
$redis->connect(
    $_ENV['REDIS_HOST'],
    $_ENV['REDIS_PORT'],
    $_ENV['REDIS_PASSWORD'] ?? null,
);

// lock `REDIS_PREFIX-queue-end` for 10 seconds to prevent a possible race condition
// while the lock is set, if its value is in the past, delete it, else wait 10ms, loop
while (!$redis->setnx($_ENV['REDIS_PREFIX'] . '-lock', time() + 10)) {
    if ($redis->get($_ENV['REDIS_PREFIX'] . '-lock') < time()) {
        $redis->del($_ENV['REDIS_PREFIX'] . '-lock');
    } else {
        // sleep for 10ms and try again
        usleep(10000);
    }
}

// `REDIS_PREFIX-queue-end` is the ID of the last message in the queue
$redis->incr($_ENV['REDIS_PREFIX'] . '-last-id'); // increment the ID by one or create the key and set to 1
$number = $redis->get($_ENV['REDIS_PREFIX'] . '-last-id'); // get the new ID

// release the lock
$redis->del($_ENV['REDIS_PREFIX'] . '-lock');

// set the message in the queue
// expires in 60 seconds (personal preference for this project)
$redis->set($_ENV['REDIS_PREFIX'] . '-message-' . $number, $_POST['message'], 60);

echo 'Success';
