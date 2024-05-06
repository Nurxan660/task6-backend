#!/usr/bin/env php
<?php

use App\WebSocket\WebSocketHandler;
use Workerman\Worker;
use Predis\Client;

require_once __DIR__ . '/vendor/autoload.php';

$client = new Client('redis-14051.c62.us-east-1-4.ec2.redns.redis-cloud.com:14051');
$handler = new WebSocketHandler($client);

$worker = new Worker("websocket://0.0.0.0:2346");
$worker->onWebSocketConnect = function ($connection, $http_header) use ($handler) {
    $handler->onWebSocketConnect($connection, $http_header);
};
$worker->onConnect = [$handler, 'onConnect'];
$worker->onMessage = [$handler, 'onMessage'];
$worker->onClose = [$handler, 'onClose'];

Worker::runAll();
