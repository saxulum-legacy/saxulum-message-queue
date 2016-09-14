<?php

namespace Saxulum\Tests\MessageQueue\SystemV;

use PHPUnit\Framework\TestCase;
use Saxulum\MessageQueue\SystemV\SystemVSend;
use Saxulum\Tests\MessageQueue\Resources\SampleMessage;

/**
 * @group unit
 * @coverage Saxulum\MessageQueue\SystemV\SystemVSend
 */
class SystemVSendTest extends TestCase
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
    
    function msg_send(
        \stdClass $queue,
        int $msgtype,
        string $message,
        bool $serialize = true,
        bool $blocking = true,
        int &$errorcode = null
    ) {
        return true;
    }
}
EOT;
        eval($cFunctions);

        $sender = new SystemVSend(1);
        $sender->send(new SampleMessage('sample1', 1, 0, 0));
    }

    /**
     * @runInSeparateProcess
     */
    public function testWithErrorCode()
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessage(
            'Can\'t send message, error code 1000, {"context":"sample1","total":1,"success":0,"failed":0}'
        );
        self::expectExceptionCode(1000);

        $cFunctions = <<<'EOT'
namespace Saxulum\MessageQueue\SystemV
{
    use Saxulum\Tests\MessageQueue\Resources\SampleMessage;
    
    function msg_get_queue(int $key): \stdClass
    {
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
        $errorcode = 1000;

        return false;
    }
}
EOT;
        eval($cFunctions);

        $sender = new SystemVSend(1);
        $sender->send(new SampleMessage('sample1', 1, 0, 0));
    }
}
