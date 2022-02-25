<?php

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
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
$redis->config('SET', 'notify-keyspace-events', 'KEA');

// echo existing messages
$messageKeys = $redis->keys($_ENV['REDIS_PREFIX'] . '-message-*');
$messages = $redis->mGet($messageKeys);
foreach ($messages as $message) {
    echo 'message => ' . $message . PHP_EOL;
}
unset($messages, $messageKeys);
ob_flush();
flush();

// echo messages as they come in
$redis->setOption(Redis::OPT_READ_TIMEOUT, 10); // so we can send a heartbeat
while (!connection_aborted()) { // while the connection is open
    try { // psubscribe() will normally throw an exception when the connection times out
        $redis->psubscribe(['__key*__:' . $_ENV['REDIS_PREFIX'] . '-message-*'], function ($redis, $pattern, $channel, $eventType) {
            if ($eventType == 'set') {
                // $messageKey = substr($channel, strlen('__keyspace@0__:'));
                echo 'message => ' . $redis->get($_ENV['REDIS_PREFIX'] . '-last-id') . PHP_EOL;
                ob_flush();
                flush();
            }
        });
    } catch (RedisException $e) {
        echo 'heartbeat' . PHP_EOL;
        ob_flush();
        flush();
    }
}
