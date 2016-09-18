<?php

namespace Saxulum\Tests\MessageQueue\Redis;

use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;
use Saxulum\MessageQueue\MessageSendException;
use Saxulum\MessageQueue\Redis\RedisSend;
use Saxulum\Tests\MessageQueue\Resources\SampleMessage;

/**
 * @group unit
 * @covers Saxulum\MessageQueue\Redis\RedisSend
 */
final class RedisSendTest extends TestCase
{
    public function testWithMessage()
    {
        $message = new SampleMessage('subprocess1', 'message 1');

        $client = $this->getClient([
            [
                'arguments' => ['messages', [$message->toJson()]],
            ],
        ]);

        $sender = new RedisSend($client, 'messages');
        $sender->send($message);
    }

    public function testWithMessageExpectMessageSendExceptionSendFailed()
    {
        self::expectException(MessageSendException::class);
        self::expectExceptionMessage(MessageSendException::MESSAGE_SEND_FAILED);
        self::expectExceptionCode(MessageSendException::CODE_SEND_FAILED);

        $message = new SampleMessage('subprocess1', 'message 1');

        $client = $this->getClient([
            [
                'arguments' => ['messages', [$message->toJson()]],
                'exception' => new \Exception('Redis error!'),
            ],
        ]);

        $sender = new RedisSend($client, 'messages');
        $sender->send($message);
    }

    /**
     * @param array $rpushStack
     *
     * @return ClientInterface
     */
    private function getClient(array $rpushStack): ClientInterface
    {
        /** @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $client */
        $client = $this
            ->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['rpush'])
            ->getMockForAbstractClass()
        ;

        $rpushCounter = 0;
        $client
            ->expects(self::any())
            ->method('rpush')
            ->willReturnCallback(
                function (string $key, array $values) use (&$rpushStack, &$rpushCounter) {
                    ++$rpushCounter;

                    $rpush = array_shift($rpushStack);

                    self::assertNotNull(
                        $rpush,
                        sprintf('There is no data left within rpushStack at %d call!', $rpushCounter)
                    );

                    self::assertSame($key, $rpush['arguments'][0]);
                    self::assertSame($values, $rpush['arguments'][1]);

                    if (isset($rpush['exception'])) {
                        throw $rpush['exception'];
                    }

                    if (isset($rpush['return'])) {
                        return $rpush['return'];
                    }
                }
            )
        ;

        return $client;
    }
}
