<?php

namespace Saxulum\MessageQueue\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Saxulum\MessageQueue\MessageInterface;
use Saxulum\MessageQueue\MessageSendException;
use Saxulum\MessageQueue\MessageSendInterface;

final class RabbitMQSend implements MessageSendInterface
{
    /**
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * @var string
     */
    private $queue;

    /**
     * @param AMQPStreamConnection $connection
     * @param string               $queue
     */
    public function __construct(AMQPStreamConnection $connection, string $queue)
    {
        $this->connection = $connection;
        $this->queue = $queue;
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
        try {
            $channel = $this->connection->channel();
            $channel->queue_declare($this->queue, false, false, false, false);
            $channel->basic_publish(new AMQPMessage($message->toJson()), '', $this->queue);
        } catch (\Exception $e) {
            throw new MessageSendException(
                MessageSendException::MESSAGE_SEND_FAILED.'('.$e->getMessage().')',
                MessageSendException::CODE_SEND_FAILED,
                $e
            );
        }

        return $this;
    }
}
