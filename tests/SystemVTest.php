<?php

namespace Saxulum\Tests\MessageQueue;

use PHPUnit\Framework\TestCase;
use Saxulum\MessageQueue\SystemV\SystemVReceive;
use Saxulum\MessageQueue\SystemV\SystemVSend;
use Saxulum\Tests\MessageQueue\Resources\SampleMessage;

class SystemVTest extends TestCase
{
    public function testCompleteWorkflow()
    {
        $sender1 = new SystemVSend(1);
        $sender2 = new SystemVSend(1);
        $sender3 = new SystemVSend(1);
        $sender4 = new SystemVSend(1);
        $sender5 = new SystemVSend(1);

        $receiver = new SystemVReceive(SampleMessage::class, 1);

        $sampleMessage1 = new SampleMessage('sender1', 1, 1, 0);
        $sampleMessage2 = new SampleMessage('sender1', 1, 1, 0);
        $sampleMessage3 = new SampleMessage('sender1', 1, 0, 1);
        $sampleMessage4 = new SampleMessage('sender2', 1, 0, 1);
        $sampleMessage5 = new SampleMessage('sender2', 1, 1, 0);
        $sampleMessage6 = new SampleMessage('sender1', 1, 1, 0);
        $sampleMessage7 = new SampleMessage('sender2', 1, 0, 0);
        $sampleMessage8 = new SampleMessage('sender3', 1, 1, 0);
        $sampleMessage9 = new SampleMessage('sender4', 1, 0, 0);
        $sampleMessage10 = new SampleMessage('sender5', 1, 1, 0);

        $sender1->send($sampleMessage1);
        $sender1->send($sampleMessage2);
        $sender1->send($sampleMessage3);
        $sender2->send($sampleMessage4);
        $sender2->send($sampleMessage5);

        $receivedMessages1 = [];
        while(null !== $receivedMessage = $receiver->receive()) {
            $receivedMessages1[] = $receivedMessage;
        }

        self::assertCount(5, $receivedMessages1);

        self::assertEquals($sampleMessage1, $receivedMessages1[0]);
        self::assertEquals($sampleMessage2, $receivedMessages1[1]);
        self::assertEquals($sampleMessage3, $receivedMessages1[2]);
        self::assertEquals($sampleMessage4, $receivedMessages1[3]);
        self::assertEquals($sampleMessage5, $receivedMessages1[4]);

        $sender1->send($sampleMessage6);
        $sender2->send($sampleMessage7);
        $sender3->send($sampleMessage8);
        $sender4->send($sampleMessage9);
        $sender5->send($sampleMessage10);

        $receivedMessages2 = [];
        while(null !== $receivedMessage = $receiver->receive()) {
            $receivedMessages2[] = $receivedMessage;
        }

        self::assertCount(5, $receivedMessages2);

        self::assertEquals($sampleMessage6, $receivedMessages2[0]);
        self::assertEquals($sampleMessage7, $receivedMessages2[1]);
        self::assertEquals($sampleMessage8, $receivedMessages2[2]);
        self::assertEquals($sampleMessage9, $receivedMessages2[3]);
        self::assertEquals($sampleMessage10, $receivedMessages2[4]);
    }
}
