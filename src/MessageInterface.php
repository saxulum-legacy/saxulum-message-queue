<?php

namespace Saxulum\MessageQueue;

interface MessageInterface
{
    /**
     * @param string $json
     * @return MessageInterface
     */
    public static function fromJson(string $json): MessageInterface;

    /**
     * @return string
     */
    public function toJson(): string;
}
