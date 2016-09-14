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
        return new \stdClass();
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
        $message = (new SampleMessage('sample1', 1, 1, 0))->toJson();
    
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
        return new \stdClass();
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
        return new \stdClass();
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
