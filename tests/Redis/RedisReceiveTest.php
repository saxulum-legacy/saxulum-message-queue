<?php

namespace Saxulum\Tests\MessageQueue\Redis;

use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;
use Saxulum\MessageQueue\MessageReceiveException;
use Saxulum\MessageQueue\Redis\RedisReceive;
use Saxulum\Tests\MessageQueue\Resources\SampleMessage;

/**
 * @group unit
 * @covers Saxulum\MessageQueue\Redis\RedisReceive
 * @covers Saxulum\MessageQueue\AbstractMessageReceive
 */
final class RedisReceiveTest extends TestCase
{
    public function testWithMessages()
    {
        $lpopStack = [];
        for ($i = 0; $i < 10; ++$i) {
            $lpopStack[] = [
                'arguments' => ['messages'],
                'return' => (new SampleMessage(1, sprintf('message %d', $i)))->toJson(),
            ];
        }

        $lpopStack[] = [
            'arguments' => ['messages'],
            'return' => null,
        ];

        $client = $this->getClient($lpopStack);

        $receiver = new RedisReceive(SampleMessage::class, $client, 'messages');

        $messages = $receiver->receiveAll();

        self::assertCount(10, $messages);
        self::assertContainsOnlyInstancesOf(SampleMessage::class, $messages);
    }

    public function testWithMessage()
    {
        $client = $this->getClient([
            [
                'arguments' => ['messages'],
                'return' => (new SampleMessage(1, 'message 1'))->toJson(),
            ],
        ]);

        $receiver = new RedisReceive(SampleMessage::class, $client, 'messages');

        self::assertInstanceOf(SampleMessage::class, $receiver->receive());
    }

    public function testWithoutMessage()
    {
        $client = $this->getClient([['arguments' => ['messages'], 'return' => null]]);

        $receiver = new RedisReceive(SampleMessage::class, $client, 'messages');

        self::assertNull($receiver->receive());
    }

    public function testWithExcetionExpectsMessageReceivedException()
    {
        self::expectException(MessageReceiveException::class);
        self::expectExceptionMessage(MessageReceiveException::MESSAGE_RECEIVE_FAILED);
        self::expectExceptionCode(MessageReceiveException::CODE_RECEIVE_FAILED);

        $client = $this->getClient([['arguments' => ['messages'], 'exception' => new \Exception()]]);

        $receiver = new RedisReceive(SampleMessage::class, $client, 'messages');
        $receiver->receive();
    }

    /**
     * @param array $lpopStack
     *
     * @return ClientInterface
     */
    private function getClient(array $lpopStack): ClientInterface
    {
        /** @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $client */
        $client = $this
            ->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['lpop'])
            ->getMockForAbstractClass()
        ;

        $lpopCounter = 0;
        $client
            ->expects(self::any())
            ->method('lpop')
            ->willReturnCallback(
                function (string $key) use (&$lpopStack, &$lpopCounter) {
                    ++$lpopCounter;

                    $lpop = array_shift($lpopStack);

                    self::assertNotNull(
                        $lpop,
                        sprintf('There is no data left within lpopStack at %d call!', $lpopCounter)
                    );

                    self::assertSame($key, $lpop['arguments'][0]);

                    if (isset($lpop['exception'])) {
                        throw $lpop['exception'];
                    }

                    return $lpop['return'];
                }
            )
        ;

        return $client;
    }
}
