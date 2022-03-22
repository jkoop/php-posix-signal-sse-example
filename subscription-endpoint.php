<?php

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit;
}

/*****/

// send existing messages
$messagesSent = [];
$messageIds = scandir(__DIR__ . '/messages');
foreach ($messageIds as $messageId) {
    if ($messageId[0] == '.') continue;

    $message = file_get_contents(__DIR__ . '/messages/' . $messageId);
    echo 'message: ' . str_replace("\n", ' ', $message) . "\n\n";

    $messagesSent[] = $messageId;
}

// register listener
register_shutdown_function('unregister_listener');
pcntl_async_signals(true);
pcntl_signal(SIGUSR1, 'send_message');
file_put_contents(__DIR__ . '/listeners/' . posix_getpid(), '');

// send messages as they come in
while (!connection_aborted()) { // while the connection is open
    sleep(15); // will be interrupted by SIGUSR1

    echo ":keep-alive\n"; // this is only an SSE comment
    ob_flush();
    flush();
}

/*****/

function unregister_listener() {
    @unlink(__DIR__ . '/listeners/' . posix_getpid());
}

function send_message() {
    global $messagesSent;

    $messageIds = scandir(__DIR__ . '/messages');
    foreach ($messageIds as $messageId) {
        if ($messageId[0] == '.' || in_array($messageId, $messagesSent)) continue;

        $message = file_get_contents(__DIR__ . '/messages/' . $messageId);
        echo 'message: ' . str_replace("\n", ' ', $message) . "\n\n";

        $messagesSent[] = $messageId;
    }

    ob_flush();
    flush();
}
