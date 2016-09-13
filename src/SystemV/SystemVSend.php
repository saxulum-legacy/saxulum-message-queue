<?php

namespace Saxulum\MessageQueue\SystemV;

use Saxulum\MessageQueue\MessageInterface;
use Saxulum\MessageQueue\MessageSendInterface;

final class SystemVSend implements MessageSendInterface
{
    /**
     * @var resource
     */
    private $queue;

    /**
     * @var int
     */
    private $type;

    /**
     * @param int $key
     * @param int $type
     * @throws \Exception
     */
    public function __construct(int $key, int $type = 1)
    {
        $this->queue = msg_get_queue($key);
        $this->type = $type;
    }

    /**
     * @param MessageInterface $message
     * @return MessageSendInterface
     * @throws \Exception
     */
    public function send(MessageInterface $message): MessageSendInterface
    {
        $json = $message->toJson();
        if (false === msg_send($this->queue, $this->type, $json, false)) {
            throw new \Exception(sprintf('Cant send message : %s', $json));
        }

        return $this;
    }
}
