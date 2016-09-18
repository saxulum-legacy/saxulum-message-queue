<?php

namespace Saxulum\MessageQueue;

interface MessageReceiveInterface
{
    /**
     * @return MessageInterface|null
     *
     * @throws MessageReceiveException
     */
    public function receive();

    /**
     * @return MessageInterface[]
     *
     * @throws MessageReceiveException
     */
    public function receiveAll(): array;
}
