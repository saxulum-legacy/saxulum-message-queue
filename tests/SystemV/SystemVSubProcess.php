#!/usr/bin/env php
<?php

require __DIR__.'/../bootstrap.php';

use Saxulum\MessageQueue\SystemV\SystemVSend;
use Saxulum\Tests\MessageQueue\Resources\SampleMessage;

if (!isset($argv[1])) {
    throw new \InvalidArgumentException('Missing key for SystemVSend');
}

if (!isset($argv[2])) {
    throw new \InvalidArgumentException('Missing child id');
}

$send = new SystemVSend($argv[1]);

for ($i = 0; $i < 100; ++$i) {
    $message = new SampleMessage($argv[2], $i, $i % 2 === 0, $i % 2 !== 0);
    $send->send($message);

    echo $message->toJson().PHP_EOL;

    usleep(mt_rand(10, 500));
}
