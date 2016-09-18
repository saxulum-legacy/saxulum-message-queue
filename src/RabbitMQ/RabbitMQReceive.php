<?php

namespace Saxulum\MessageQueue\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Saxulum\MessageQueue\AbstractMessageReceive;
use Saxulum\MessageQueue\MessageInterface;
use Saxulum\MessageQueue\MessageReceiveException;

final class RabbitMQReceive extends AbstractMessageReceive
{
    /**
     * @var string
     */
    private $messageClass;

    /**
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * @var string
     */
    private $queue;

    /**
     * @param string               $messageClass
     * @param AMQPStreamConnection $connection
     * @param string               $queue
     */
    public function __construct(string $messageClass, AMQPStreamConnection $connection, string $queue)
    {
        $this->messageClass = $messageClass;
        $this->connection = $connection;
        $this->queue = $queue;
    }

    /**
     * @return MessageInterface|null
     *
     * @throws MessageReceiveException
     */
    public function receive()
    {
        try {
            $channel = $this->connection->channel();
            $channel->queue_declare($this->queue, false, false, false, false);

            /** @var AMQPMessage $rabbitMQMessage */
            if (null === $rabbitMQMessage = $channel->basic_get($this->queue, true)) {
                return null;
            }

            /** @var MessageInterface $messageClass */
            $messageClass = $this->messageClass;

            return $messageClass::fromJson($rabbitMQMessage->body);
        } catch (\Exception $e) {
            throw new MessageReceiveException(
                MessageReceiveException::MESSAGE_RECEIVE_FAILED.'('.$e->getMessage().')',
                MessageReceiveException::CODE_RECEIVE_FAILED,
                $e
            );
        }
    }
}
