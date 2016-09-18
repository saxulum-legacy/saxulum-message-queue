# saxulum-message-queue

[![Build Status](https://api.travis-ci.org/saxulum/saxulum-message-queue.png?branch=master)](https://travis-ci.org/saxulum/saxulum-message-queue)
[![Total Downloads](https://poser.pugx.org/saxulum/saxulum-message-queue/downloads.png)](https://packagist.org/packages/saxulum/saxulum-message-queue)
[![Latest Stable Version](https://poser.pugx.org/saxulum/saxulum-message-queue/v/stable.png)](https://packagist.org/packages/saxulum/saxulum-message-queue)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/saxulum/saxulum-message-queue/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/saxulum/saxulum-message-queue/?branch=master)

## Description

A simple to use messaging queue abstraction.

## Requirements

 * php: ~7.0

## Installation

Through [Composer](http://getcomposer.org) as [saxulum/saxulum-message-queue][1].

## Usage

### Message

```{.php}
<?php

namespace My\Project;

use Saxulum\MessageQueue\MessageInterface;

class SampleMessage implements MessageInterface
{
    /**
     * @var string
     */
    private $context;

    /**
     * @var string
     */
    private $message;

    /**
     * @param string $context
     * @param string $message
     */
    public function __construct(string $context, string $message)
    {
        $this->context = $context;
        $this->message = $message;
    }

    /**
     * @param string $json
     *
     * @return MessageInterface
     */
    public static function fromJson(string $json): MessageInterface
    {
        $rawMessage = json_decode($json);

        return new self($rawMessage->context, $rawMessage->message);
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode([
            'context' => $this->context,
            'message' => $this->message,
        ]);
    }

    /**
     * @return string
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
```

### Redis

#### Send

```{.php}
<?php

use My\Project\SampleMessage;
use Predis\Client;
use Saxulum\MessageQueue\Redis\RedisSend;

$client = new Client();
$sender = new RedisSend($client, 'messages');
$sender->send(new SampleMessage('context', 'this is a message'));
```

#### Receive

```{.php}
<?php

use My\Project\SampleMessage;
use Predis\Client;
use Saxulum\MessageQueue\Redis\RedisReceive;

$client = new Client();
$sender = new RedisReceive(SampleMessage::class, $client, 'messages');
$message = $sender->receive();
```

### SystemV

#### Send

```{.php}
<?php

use My\Project\SampleMessage;
use Saxulum\MessageQueue\SystemV\SystemVSend;

$sender = new SystemVSend(1);
$sender->send(new SampleMessage('context', 'this is a message'));
```

#### Receive

```{.php}
<?php

use My\Project\SampleMessage;
use Saxulum\MessageQueue\SystemV\SystemVReceive;

$sender = new SystemVReceive(SampleMessage::class, 1);
$message = $sender->receive();
```

[1]: https://packagist.org/packages/saxulum/saxulum-message-queue

## Copyright

Dominik Zogg 2016
