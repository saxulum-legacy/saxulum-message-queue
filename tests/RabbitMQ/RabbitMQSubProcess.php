#!/usr/bin/env php
<?php

require __DIR__.'/../bootstrap.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Saxulum\MessageQueue\RabbitMQ\RabbitMQSend;
use Saxulum\Tests\MessageQueue\Resources\SampleMessage;

if (!isset($argv[1])) {
    throw new \InvalidArgumentException('Missing queue for RabbitMQSend');
}

if (!isset($argv[2])) {
    throw new \InvalidArgumentException('Missing child id');
}

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$send = new RabbitMQSend($connection, $argv[1]);

for ($i = 0; $i < 100; ++$i) {
    $message = new SampleMessage($argv[2], sprintf('message %d', $i));
    $send->send($message);

    echo $message->toJson().PHP_EOL;

    usleep(mt_rand(10, 500));
}
