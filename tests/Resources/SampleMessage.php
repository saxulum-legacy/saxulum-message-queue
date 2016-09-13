<?php

namespace Saxulum\Tests\MessageQueue\Resources;

use Saxulum\MessageQueue\MessageInterface;

class SampleMessage implements MessageInterface
{
    /**
     * @var string
     */
    private $context;

    /**
     * @var int
     */
    private $total;

    /**
     * @var int
     */
    private $success;

    /**
     * @var int
     */
    private $failed;

    /**
     * @param string $json
     *
     * @return MessageInterface
     */
    public static function fromJson(string $json): MessageInterface
    {
        $rawMessage = json_decode($json);

        return new self($rawMessage->context, $rawMessage->total, $rawMessage->success, $rawMessage->failed);
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode([
            'context' => $this->context,
            'total' => $this->total,
            'success' => $this->success,
            'failed' => $this->failed,
        ]);
    }

    /**
     * @param string $context
     * @param int    $total
     * @param int    $success
     * @param int    $failed
     */
    public function __construct(string $context, int $total, int $success, int $failed)
    {
        $this->context = $context;
        $this->total = $total;
        $this->success = $success;
        $this->failed = $failed;
    }

    /**
     * @return string
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getSuccess(): int
    {
        return $this->success;
    }

    /**
     * @return int
     */
    public function getFailed(): int
    {
        return $this->failed;
    }
}
