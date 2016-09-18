<?php

namespace Saxulum\MessageQueue;

interface MessageSendInterface
{
    /**
     * @param MessageInterface $message
     *
     * @return MessageSendInterface
     *
     * @throws MessageSendException
     */
    public function send(MessageInterface $message): MessageSendInterface;
}
