<?php

namespace Saxulum\MessageQueue\SystemV;

use Saxulum\MessageQueue\MessageInterface;
use Saxulum\MessageQueue\MessageSendException;
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
     */
    public function __construct(int $key, int $type = 1)
    {
        $this->queue = msg_get_queue($key);
        $this->type = $type;
    }

    /**
     * @param MessageInterface $message
     *
     * @return MessageSendInterface
     *
     * @throws MessageSendException
     */
    public function send(MessageInterface $message): MessageSendInterface
    {
        $errorCode = null;
        $json = $message->toJson();

        if (false === msg_send($this->queue, $this->type, $json, false, true, $errorCode)) {
            throw new MessageSendException(
                sprintf(MessageSendException::MESSAGE_SEND_FAILED, sprintf(' (SystemV error code %d)', $errorCode)),
                MessageSendException::CODE_SEND_FAILED
            );
        }

        return $this;
    }
}
