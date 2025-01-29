<?php

declare(strict_types=1);

namespace SdkEsb;

use SdkEsb\Rabbit\Config\ConsumeConfig;

interface ConsumerInterface
{
    /**
     * @param ConsumeConfig $config
     * @return void
     */
    public function consumeMessage(ConsumeConfig $config): void;
}
