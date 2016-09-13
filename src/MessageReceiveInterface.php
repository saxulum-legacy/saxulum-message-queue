<?php

namespace Saxulum\MessageQueue;

interface MessageReceiveInterface
{
    /**
     * @return MessageInterface|null
     */
    public function receive();
}
