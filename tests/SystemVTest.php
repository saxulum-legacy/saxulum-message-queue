<?php

namespace Saxulum\Tests\MessageQueue;

use PHPUnit\Framework\TestCase;
use Saxulum\MessageQueue\SystemV\SystemVReceive;
use Saxulum\Tests\MessageQueue\Resources\SampleMessage;
use Symfony\Component\Process\Process;

class SystemVTest extends TestCase
{
    public function testWithSubProcess()
    {
        $subProcessPath = __DIR__.'/Resources/SystemVSubProcess.php';

        /** @var Process[] $subProcesses */
        $subProcesses = [];
        for ($i = 1; $i <= 5; ++$i) {
            $subProcesses[] = new Process($subProcessPath.' 1 '.$i);
        }

        $output = '';
        $errorOutput = '';

        foreach ($subProcesses as $subProcess) {
            $subProcess->start(function ($type, $buffer) use (&$output, &$errorOutput) {
                if (Process::OUT === $type) {
                    $output .= $buffer;
                } elseif (Process::ERR === $type) {
                    $errorOutput .= $buffer;
                }
            });
        }

        $receive = new SystemVReceive(SampleMessage::class, 1);

        /** @var SampleMessage[] $receivedMessages */
        $receivedMessages = [];

        do {
            $subProcessesRunning = [];

            foreach ($subProcesses as $subProcess) {
                if ($subProcess->isRunning()) {
                    $subProcessesRunning[] = $subProcess;
                }
            }

            while (null !== $receivedMessage = $receive->receive()) {
                $receivedMessages[] = $receivedMessage;
            }
        } while ([] !== $subProcessesRunning);

        $receivedMessagesBySubProcesses = [];
        foreach ($receivedMessages as $receivedMessage) {
            $context = $receivedMessage->getContext();
            if (!isset($receivedMessagesBySubProcesses[$context])) {
                $receivedMessagesBySubProcesses[$context] = [];
            }
            $receivedMessagesBySubProcesses[$context][] = $receivedMessage;
        }

        self::assertSame(24950, strlen($output));
        self::assertEmpty($errorOutput, $errorOutput);
        self::assertCount(500, $receivedMessages);
        self::assertCount(5, $receivedMessagesBySubProcesses);
    }
}
