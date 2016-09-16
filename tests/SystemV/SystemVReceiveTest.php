<?php

namespace Saxulum\Tests\MessageQueue\SystemV;

use PHPUnit\Framework\TestCase;
use Saxulum\MessageQueue\SystemV\SystemVReceive;
use Saxulum\Tests\MessageQueue\Resources\SampleMessage;

/**
 * @group unit
 * @coverage Saxulum\MessageQueue\SystemV\SystemVReceive
 */
class SystemVReceiveTest extends TestCase
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
    
    function msg_set_queue(\stdClass $queue, array $data)
    {
        \PHPUnit\Framework\TestCase::assertEquals(['msg_qbytes' => 16384], $data);

        return true;
    }

    function msg_receive(
        \stdClass $queue,
        int $desiredmsgtype,
        int &$msgtype = null,
        int $maxsize,
        string &$message = null,
        bool $unserialize = true,
        int $flags = 0,
        int &$errorcode = null
    ): bool {
        \PHPUnit\Framework\TestCase::assertSame(1, $desiredmsgtype);
        \PHPUnit\Framework\TestCase::assertNull($msgtype);
        \PHPUnit\Framework\TestCase::assertSame(8192, $maxsize);
        \PHPUnit\Framework\TestCase::assertNull($message);
        \PHPUnit\Framework\TestCase::assertFalse($unserialize);
        \PHPUnit\Framework\TestCase::assertSame(MSG_IPC_NOWAIT, $flags);
        \PHPUnit\Framework\TestCase::assertNull($errorcode);

        $message = (new SampleMessage('subprocess1', 'message 1'))->toJson();
    
        return true;
    }
}
EOT;
        eval($cFunctions);

        $receiver = new SystemVReceive(SampleMessage::class, 1);
        $receiver->receive();
    }

    /**
     * @runInSeparateProcess
     */
    public function testWithoutMessage()
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

    function msg_set_queue(\stdClass $queue, array $data)
    {
        \PHPUnit\Framework\TestCase::assertEquals(['msg_qbytes' => 16384], $data);

        return true;
    }
    
    function msg_receive(
        \stdClass $queue,
        int $desiredmsgtype,
        int &$msgtype = null,
        int $maxsize,
        string &$message = null,
        bool $unserialize = true,
        int $flags = 0,
        int &$errorcode = null
    ): bool {
        \PHPUnit\Framework\TestCase::assertSame(1, $desiredmsgtype);
        \PHPUnit\Framework\TestCase::assertNull($msgtype);
        \PHPUnit\Framework\TestCase::assertSame(8192, $maxsize);
        \PHPUnit\Framework\TestCase::assertNull($message);
        \PHPUnit\Framework\TestCase::assertFalse($unserialize);
        \PHPUnit\Framework\TestCase::assertSame(MSG_IPC_NOWAIT, $flags);
        \PHPUnit\Framework\TestCase::assertNull($errorcode);

        $errorcode = MSG_ENOMSG;

        return false;
    }
}
EOT;
        eval($cFunctions);

        $receiver = new SystemVReceive(SampleMessage::class, 1);
        $receiver->receive();
    }

    /**
     * @runInSeparateProcess
     */
    public function testWithErrorCode()
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessage('Can\'t receive message, error code 1000');
        self::expectExceptionCode(1000);

        $cFunctions = <<<'EOT'
namespace Saxulum\MessageQueue\SystemV
{
    use Saxulum\Tests\MessageQueue\Resources\SampleMessage;

    function msg_get_queue(int $key): \stdClass
    {
        \PHPUnit\Framework\TestCase::assertSame(1, $key);

        return new \stdClass();
    }

    function msg_set_queue(\stdClass $queue, array $data)
    {
        \PHPUnit\Framework\TestCase::assertEquals(['msg_qbytes' => 16384], $data);

        return true;
    }
    
    function msg_receive(
        \stdClass $queue,
        int $desiredmsgtype,
        int &$msgtype = null,
        int $maxsize,
        string &$message = null,
        bool $unserialize = true,
        int $flags = 0,
        int &$errorcode = null
    ): bool {
        \PHPUnit\Framework\TestCase::assertSame(1, $desiredmsgtype);
        \PHPUnit\Framework\TestCase::assertNull($msgtype);
        \PHPUnit\Framework\TestCase::assertSame(8192, $maxsize);
        \PHPUnit\Framework\TestCase::assertNull($message);
        \PHPUnit\Framework\TestCase::assertFalse($unserialize);
        \PHPUnit\Framework\TestCase::assertSame(MSG_IPC_NOWAIT, $flags);
        \PHPUnit\Framework\TestCase::assertNull($errorcode);

        $errorcode = 1000;

        return false;
    }
}
EOT;
        eval($cFunctions);

        $receiver = new SystemVReceive(SampleMessage::class, 1);
        $receiver->receive();
    }
}
