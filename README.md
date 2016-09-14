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

### SystemV

#### Send

```{.php}
<?php

use Saxulum\MessageQueue\SystemV\SystemVSend;

$sender = new SystemVSend(1);
$sender->send(new <MessageInterface>);
```

#### Receive

```{.php}
<?php

use Saxulum\MessageQueue\SystemV\SystemVReceive;

$sender = new SystemVReceive(<MessageInterface::class>, 1);
$message = $sender->receive();
```

[1]: https://packagist.org/packages/saxulum/saxulum-message-queue

## Copyright

Dominik Zogg 2016
