<?php

namespace Saxulum\MessageQueue\Redis;

use Predis\ClientInterface;
use Saxulum\MessageQueue\AbstractMessageReceive;
use Saxulum\MessageQueue\MessageInterface;
use Saxulum\MessageQueue\MessageReceiveException;

final class RedisReceive extends AbstractMessageReceive
{
    /**
     * @var string
     */
    private $messageClass;

    /**
     * @var ClientInterface
     */
    private $redis;

    /**
     * @var string
     */
    private $list;

    /**
     * @param string          $messageClass
     * @param ClientInterface $redis
     * @param string          $list
     */
    public function __construct(string $messageClass, ClientInterface $redis, string $list)
    {
        $this->messageClass = $messageClass;
        $this->redis = $redis;
        $this->list = $list;
    }

    /**
     * @return MessageInterface|null
     *
     * @throws MessageReceiveException
     */
    public function receive()
    {
        try {
            if (null === $json = $this->redis->lpop($this->list)) {
                return null;
            }

            /** @var MessageInterface $messageClass */
            $messageClass = $this->messageClass;

            return $messageClass::fromJson($json);
        } catch (\Exception $e) {
            throw new MessageReceiveException(
                MessageReceiveException::MESSAGE_RECEIVE_FAILED.'('.$e->getMessage().')',
                MessageReceiveException::CODE_RECEIVE_FAILED,
                $e
            );
        }
    }

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
