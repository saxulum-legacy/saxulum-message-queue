<?php

namespace Saxulum\Tests\MessageQueue\SystemV;

use PHPUnit\Framework\TestCase;
use Saxulum\MessageQueue\MessageSendException;
use Saxulum\MessageQueue\SystemV\SystemVSend;
use Saxulum\Tests\MessageQueue\Resources\SampleMessage;

/**
 * @group unit
 * @covers Saxulum\MessageQueue\SystemV\SystemVSend
 */
final class SystemVSendTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testWithMessage()
    {
        $cFunctions = <<<'EOT'
namespace Saxulum\MessageQueue\SystemV
{
    use Saxulum\Tests\MessageQueue\Resources\SampleMessage;
    
    function msg_get_queue(int $key): \stdClass
    {
        \PHPUnit\Framework\TestCase::assertSame(1, $key);

        return new \stdClass();
    }
    
    function msg_send(
        \stdClass $queue,
        int $msgtype,
        string $message,
        bool $serialize = true,
        bool $blocking = true,
        int &$errorcode = null
    ) {
        \PHPUnit\Framework\TestCase::assertSame(1, $msgtype);
        \PHPUnit\Framework\TestCase::assertSame((new SampleMessage('subprocess1', 'message 1'))->toJson(), $message);
        \PHPUnit\Framework\TestCase::assertFalse($serialize);
        \PHPUnit\Framework\TestCase::assertTrue($blocking);
        \PHPUnit\Framework\TestCase::assertNull($errorcode);

        return true;
    }
}
EOT;
        eval($cFunctions);

        $sender = new SystemVSend(1);
        $sender->send(new SampleMessage('subprocess1', 'message 1'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testWithMessageExpectMessageSendExceptionSendFailed()
    {
        self::expectException(MessageSendException::class);
        self::expectExceptionMessage(MessageSendException::MESSAGE_SEND_FAILED);
        self::expectExceptionCode(MessageSendException::CODE_SEND_FAILED);

        $cFunctions = <<<'EOT'
namespace Saxulum\MessageQueue\SystemV
{
    use Saxulum\Tests\MessageQueue\Resources\SampleMessage;
    
    function msg_get_queue(int $key): \stdClass
    {
        \PHPUnit\Framework\TestCase::assertSame(1, $key);

        return new \stdClass();
    }
    
    function msg_send(
        \stdClass $queue,
        int $msgtype,
        string $message,
        bool $serialize = true,
        bool $blocking = true,
        int &$errorcode = null
    ) {
        \PHPUnit\Framework\TestCase::assertSame(1, $msgtype);
        \PHPUnit\Framework\TestCase::assertSame((new SampleMessage('subprocess1', 'message 1'))->toJson(), $message);
        \PHPUnit\Framework\TestCase::assertFalse($serialize);
        \PHPUnit\Framework\TestCase::assertTrue($blocking);
        \PHPUnit\Framework\TestCase::assertNull($errorcode);

        $errorcode = 1000;

        return false;
    }
}
EOT;
        eval($cFunctions);

        $sender = new SystemVSend(1);
        $sender->send(new SampleMessage('subprocess1', 'message 1'));
    }
}
