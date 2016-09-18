<?php

namespace Saxulum\MessageQueue;

class MessageReceiveException extends \RuntimeException
{
    const MESSAGE_RECEIVE_FAILED = 'Can\'t receive message';

    const CODE_RECEIVE_FAILED = 1;
}
