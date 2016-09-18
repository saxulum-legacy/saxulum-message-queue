<?php

namespace Saxulum\MessageQueue\Redis;

use Predis\ClientInterface;
use Saxulum\MessageQueue\MessageInterface;
use Saxulum\MessageQueue\MessageSendException;
use Saxulum\MessageQueue\MessageSendInterface;

final class RedisSend implements MessageSendInterface
{
    /**
     * @var ClientInterface
     */
    private $redis;

    /**
     * @var string
     */
    private $list;

    /**
     * @param ClientInterface $redis
     * @param string          $list
     */
    public function __construct(ClientInterface $redis, string $list)
    {
        $this->redis = $redis;
        $this->list = $list;
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
            $this->redis->rpush($this->list, [$message->toJson()]);
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
