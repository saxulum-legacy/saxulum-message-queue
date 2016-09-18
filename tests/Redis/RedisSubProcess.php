#!/usr/bin/env php
<?php

require __DIR__.'/../bootstrap.php';

use Predis\Client;
use Saxulum\MessageQueue\Redis\RedisSend;
use Saxulum\Tests\MessageQueue\Resources\SampleMessage;

if (!isset($argv[1])) {
    throw new \InvalidArgumentException('Missing list for RedisSend');
}

if (!isset($argv[2])) {
    throw new \InvalidArgumentException('Missing child id');
}

$send = new RedisSend(new Client(), $argv[1]);

for ($i = 0; $i < 100; ++$i) {
    $message = new SampleMessage($argv[2], sprintf('message %d', $i));
    $send->send($message);

    echo $message->toJson().PHP_EOL;

    usleep(mt_rand(10, 500));
}
