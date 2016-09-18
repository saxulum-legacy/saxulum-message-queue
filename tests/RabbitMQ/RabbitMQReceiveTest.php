<?php

namespace Saxulum\Tests\MessageQueue\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Saxulum\MessageQueue\MessageReceiveException;
use Saxulum\MessageQueue\RabbitMQ\RabbitMQReceive;
use Saxulum\Tests\MessageQueue\Resources\SampleMessage;

/**
 * @group unit
 * @covers Saxulum\MessageQueue\RabbitMQ\RabbitMQReceive
 * @covers Saxulum\MessageQueue\AbstractMessageReceive
 */
final class RabbitMQReceiveTest extends TestCase
{
    public function testWithMessages()
    {
        $basicGetStack = [];
        for ($i = 0; $i < 10; ++$i) {
            $basicGetStack[] = [
                'arguments' => ['messages', true],
                'return' => new AMQPMessage((new SampleMessage(1, sprintf('message %d', $i)))->toJson()),
            ];
        }

        $basicGetStack[] = [
            'arguments' => ['messages', true],
            'return' => null,
        ];

        $connection = $this->getConnection($basicGetStack);
        $receiver = new RabbitMQReceive(SampleMessage::class, $connection, 'messages');

        $messages = $receiver->receiveAll();

        self::assertCount(10, $messages);
        self::assertContainsOnlyInstancesOf(SampleMessage::class, $messages);
    }

    public function testWithMessage()
    {
        $connection = $this->getConnection([
            [
                'arguments' => ['messages', true],
                'return' => new AMQPMessage((new SampleMessage(1, 'message 1'))->toJson()),
            ],
        ]);

        $receiver = new RabbitMQReceive(SampleMessage::class, $connection, 'messages');

        self::assertInstanceOf(SampleMessage::class, $receiver->receive());
    }

    public function testWithoutMessage()
    {
        $connection = $this->getConnection([
            [
                'arguments' => ['messages', true],
                'return' => null,
            ],
        ]);

        $receiver = new RabbitMQReceive(SampleMessage::class, $connection, 'messages');

        self::assertNull($receiver->receive());
    }

    public function testWithExcetionExpectsMessageReceivedException()
    {
        self::expectException(MessageReceiveException::class);
        self::expectExceptionMessage(MessageReceiveException::MESSAGE_RECEIVE_FAILED);
        self::expectExceptionCode(MessageReceiveException::CODE_RECEIVE_FAILED);

        $connection = $this->getConnection([
            [
                'arguments' => ['messages', true],
                'return' => new \Exception(),
            ],
        ]);

        $receiver = new RabbitMQReceive(SampleMessage::class, $connection, 'messages');
        $receiver->receive();
    }

    /**
     * @param array $basicGetStack
     *
     * @return AMQPStreamConnection
     */
    private function getConnection(array $basicGetStack): AMQPStreamConnection
    {
        /** @var AMQPStreamConnection|\PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this
            ->getMockBuilder(AMQPStreamConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['channel'])
            ->getMock()
        ;

        $channel = $this->getChannel($basicGetStack);
        $connection->expects(self::any())->method('channel')->willReturn($channel);

        return $connection;
    }

    /**
     * @param array $basicGetStack
     *
     * @return AMQPChannel
     */
    private function getChannel(array $basicGetStack): AMQPChannel
    {
        /* @var AMQPChannel|\PHPUnit_Framework_MockObject_MockObject $connection */
        $channel = $this
            ->getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->setMethods(['queue_declare', 'basic_get'])
            ->getMock()
        ;

        $channel
            ->expects(self::any())
            ->method('queue_declare')
            ->willReturnCallback(
                function (
                    string $queue,
                    bool $passive,
                    bool $durable,
                    bool $exclusive,
                    bool $auto_delete
                ) {
                    self::assertSame($queue, 'messages');
                    self::assertFalse($passive);
                    self::assertFalse($durable);
                    self::assertFalse($exclusive);
                    self::assertFalse($auto_delete);
                }
            )
        ;

        $basicGetCounter = 0;
        $channel
            ->expects(self::any())
            ->method('basic_get')
            ->willReturnCallback(
                function (
                    string $queue,
                    bool $no_ack
                ) use (&$basicGetStack, &$basicGetCounter) {
                    ++$basicGetCounter;

                    $basicGet = array_shift($basicGetStack);

                    self::assertNotNull(
                        $basicGet,
                        sprintf('There is no data left within basicGetStack at %d call!', $basicGetCounter)
                    );

                    self::assertSame($queue, $basicGet['arguments'][0]);
                    self::assertSame($no_ack, $basicGet['arguments'][1]);

                    if (isset($basicGet['exception'])) {
                        throw $basicGet['exception'];
                    }

                    if (isset($basicGet['return'])) {
                        return $basicGet['return'];
                    }
                }
            )
        ;

        return $channel;
    }
}
