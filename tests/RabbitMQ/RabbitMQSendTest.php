<?php

namespace Saxulum\Tests\MessageQueue\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Saxulum\MessageQueue\MessageSendException;
use Saxulum\MessageQueue\RabbitMQ\RabbitMQSend;
use Saxulum\Tests\MessageQueue\Resources\SampleMessage;

/**
 * @group unit
 * @covers Saxulum\MessageQueue\RabbitMQ\RabbitMQSend
 */
final class RabbitMQSendTest extends TestCase
{
    public function testWithMessage()
    {
        $message = new SampleMessage('subprocess1', 'message 1');

        $connection = $this->getConnection(
            [
                ['arguments' => [$message->toJson(), '', 'messages']],
            ]
        );

        $sender = new RabbitMQSend($connection, 'messages');
        $sender->send($message);
    }

    public function testWithMessageExpectMessageSendExceptionSendFailed()
    {
        self::expectException(MessageSendException::class);
        self::expectExceptionMessage(MessageSendException::MESSAGE_SEND_FAILED);
        self::expectExceptionCode(MessageSendException::CODE_SEND_FAILED);

        $message = new SampleMessage('subprocess1', 'message 1');

        $connection = $this->getConnection(
            [
                ['arguments' => [$message->toJson(), '', 'messages'], 'exception' => new \Exception('RabbitMQ error!')],
            ]
        );

        $sender = new RabbitMQSend($connection, 'messages');
        $sender->send($message);
    }

    /**
     * @param array $basicPublishStack
     *
     * @return AMQPStreamConnection
     */
    private function getConnection(array $basicPublishStack): AMQPStreamConnection
    {
        /** @var AMQPStreamConnection|\PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this
            ->getMockBuilder(AMQPStreamConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['channel'])
            ->getMock()
        ;

        $channel = $this->getChannel($basicPublishStack);
        $connection->expects(self::any())->method('channel')->willReturn($channel);

        return $connection;
    }

    /**
     * @param array $basicPublishStack
     *
     * @return AMQPChannel
     */
    private function getChannel(array $basicPublishStack): AMQPChannel
    {
        /* @var AMQPChannel|\PHPUnit_Framework_MockObject_MockObject $connection */
        $channel = $this
            ->getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->setMethods(['queue_declare', 'basic_publish'])
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

        $basicPublishCounter = 0;
        $channel
            ->expects(self::any())
            ->method('basic_publish')
            ->willReturnCallback(
                function (
                    AMQPMessage $msg,
                    string $exchange,
                    string $routing_key
                ) use (&$basicPublishStack, &$basicPublishCounter) {
                    ++$basicPublishCounter;

                    $basicPublish = array_shift($basicPublishStack);

                    self::assertNotNull(
                        $basicPublish,
                        sprintf('There is no data left within basicPublishStack at %d call!', $basicPublishCounter)
                    );

                    self::assertSame($msg->body, $basicPublish['arguments'][0]);
                    self::assertSame($exchange, $basicPublish['arguments'][1]);
                    self::assertSame($routing_key, $basicPublish['arguments'][2]);

                    if (isset($basicPublish['exception'])) {
                        throw $basicPublish['exception'];
                    }

                    if (isset($basicPublish['return'])) {
                        return $basicPublish['return'];
                    }
                }
            )
        ;

        return $channel;
    }
}
