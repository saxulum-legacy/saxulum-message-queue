<?php

namespace Saxulum\MessageQueue;

class MessageSendException extends \RuntimeException
{
    const MESSAGE_SEND_FAILED = 'Can\'t send message';

    const CODE_SEND_FAILED = 1;
}
