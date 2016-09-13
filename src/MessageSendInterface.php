<?php

namespace Saxulum\MessageQueue;

interface MessageSendInterface
{
    /**
     * @param MessageInterface $message
     * @return MessageSendInterface
     */
    public function send(MessageInterface $message): MessageSendInterface;
}
