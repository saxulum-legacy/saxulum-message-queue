<?php

namespace Saxulum\MessageQueue\SystemV;

use Saxulum\MessageQueue\AbstractMessageReceive;
use Saxulum\MessageQueue\MessageInterface;
use Saxulum\MessageQueue\MessageReceiveException;

final class SystemVReceive extends AbstractMessageReceive
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
     * @var int
     */
    private $maxBytesPerMessage;

    /**
     * @param string $messageClass
     * @param int    $key
     * @param int    $type
     * @param int    $qbytes
     */
    public function __construct(string $messageClass, int $key, int $type = 1, int $qbytes = 16384)
    {
        $this->messageClass = $messageClass;
        $this->queue = msg_get_queue($key);
        $this->type = $type;
        $this->maxBytesPerMessage = $qbytes / 2;

        msg_set_queue($this->queue, ['msg_qbytes' => $qbytes]);
    }

    /**
     * @return null|MessageInterface
     *
     * @throws MessageReceiveException
     */
    public function receive()
    {
        $type = null;
        $json = null;
        $errorCode = null;

        $status = msg_receive(
            $this->queue,
            $this->type,
            $type,
            $this->maxBytesPerMessage,
            $json,
            false,
            MSG_IPC_NOWAIT,
            $errorCode
        );

        if (false === $status) {
            // we do not wait for a message (prevent lock)
            if (MSG_ENOMSG === $errorCode) {
                return null;
            }

            throw new MessageReceiveException(
                sprintf(
                    MessageReceiveException::MESSAGE_RECEIVE_FAILED,
                    sprintf(' (SystemV error code %d)', $errorCode)
                ),
                MessageReceiveException::CODE_RECEIVE_FAILED
            );
        }

        /** @var MessageInterface $messageClass */
        $messageClass = $this->messageClass;

        return $messageClass::fromJson($json);
    }
}
