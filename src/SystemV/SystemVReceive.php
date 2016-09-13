<?php

namespace Saxulum\MessageQueue\SystemV;

use Saxulum\MessageQueue\MessageInterface;
use Saxulum\MessageQueue\MessageReceiveInterface;

final class SystemVReceive implements MessageReceiveInterface
{
    /**
     * @var string
     */
    private $messageClass;

    /**
     * @var resource
     */
    private $queue;

    /**
     * @var int
     */
    private $type;

    /**
     * @param string $messageClass
     * @param int $key
     * @param int $type
     * @throws \Exception
     */
    public function __construct(string $messageClass, int $key, int $type = 1)
    {
        $this->messageClass = $messageClass;
        $this->queue = msg_get_queue($key);
        $this->type = $type;
    }

    /**
     * @return null|MessageInterface
     * @throws \Exception
     */
    public function receive()
    {
        $type = null;
        $json = null;
        $error = null;

        $status = msg_receive($this->queue, $this->type, $type, 1048576, $json, false, MSG_IPC_NOWAIT, $error);

        if (false === $status) {
            // we do not wait for a message (prevent lock)
            if (MSG_ENOMSG === $error) {
                return null;
            }

            throw new \Exception(sprintf('Can\'t receive message, error code %d', $error));
        }

        /** @var MessageInterface $messageClass */
        $messageClass = $this->messageClass;

        return $messageClass::fromJson($json);
    }
}
