<?php

declare(strict_types=1);

namespace SdkEsb;

interface HandlerInterface
{
    public function execute(string $message): void;
}