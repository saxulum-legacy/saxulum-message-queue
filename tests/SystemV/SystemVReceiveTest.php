<?php

namespace Saxulum\Tests\MessageQueue\SystemV;

use PHPUnit\Framework\TestCase;
use Saxulum\MessageQueue\MessageReceiveException;
use Saxulum\MessageQueue\SystemV\SystemVReceive;
use Saxulum\Tests\MessageQueue\Resources\SampleMessage;

/**
 * @group unit
 * @covers Saxulum\MessageQueue\SystemV\SystemVReceive
 * @covers Saxulum\MessageQueue\AbstractMessageReceive
 */
final class SystemVReceiveTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testWithMessages()
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
        
        static $i;
        if (null === $i) {
            $i = 0;
        }
        
        if ($i < 10) {
            $message = (new SampleMessage('subprocess1', 'message '.$i))->toJson();
            $i++;
            
            return true;
        }

        $errorcode = MSG_ENOMSG;

        return false;
    }
}
EOT;
        eval($cFunctions);

        $receiver = new SystemVReceive(SampleMessage::class, 1);

        $messages = $receiver->receiveAll();

        self::assertCount(10, $messages);
        self::assertContainsOnlyInstancesOf(SampleMessage::class, $messages);
    }

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

        self::assertInstanceOf(SampleMessage::class, $receiver->receive());
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

        self::assertNull($receiver->receive());
    }

    /**
     * @runInSeparateProcess
     */
    public function testWithExcetionExpectsMessageReceivedException()
    {
        self::expectException(MessageReceiveException::class);
        self::expectExceptionMessage(MessageReceiveException::MESSAGE_RECEIVE_FAILED);
        self::expectExceptionCode(MessageReceiveException::CODE_RECEIVE_FAILED);

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
