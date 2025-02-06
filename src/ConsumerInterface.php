<?php

declare(strict_types=1);

namespace SdkEsb;

interface ConsumerInterface
{
    /**
     * @param string $queue
     * @param HandlerInterface $handler
     * @return void
     */
    public function consumeMessage(string $queue, HandlerInterface $handler): void;
}
