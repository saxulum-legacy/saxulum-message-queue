<?php

namespace Saxulum\MessageQueue;

abstract class AbstractMessageReceive implements MessageReceiveInterface
{
    /**
     * @return array
     *
     * @throws MessageReceiveException
     */
    public function receiveAll(): array
    {
        $messages = [];
        while (null !== $message = $this->receive()) {
            $messages[] = $message;
        }

        return $messages;
    }
}
