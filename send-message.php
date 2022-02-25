<?php

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit;
}

/*****/

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required(['REDIS_HOST', 'REDIS_PORT', 'REDIS_PREFIX', 'MESSAGE_TTL']);

$redis = new Redis; // this requires php-redis (https://github.com/phpredis/phpredis)
$redis->connect(
    $_ENV['REDIS_HOST'],
    $_ENV['REDIS_PORT'],
);
$redis->setOption(Redis::OPT_PREFIX, $_ENV['REDIS_PREFIX'] . ':');

// `REDIS_PREFIX-queue-end` is the ID of the last message in the queue
// increment the ID by one or create the key and set to 1; returns the new value; probably thread-safe
$newMessageId = $redis->incr('last-id');

$messageText = $_SERVER['REMOTE_ADDR'] . ': ' . $_POST['message'];

// set the message in the queue
// expires in 60 seconds (personal preference for this project)
$redis->set('message-' . $newMessageId, $messageText, $_ENV['MESSAGE_TTL']);

echo 'Success';
