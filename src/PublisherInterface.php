<?php

declare(strict_types=1);

namespace SdkEsb;

use SdkEsb\Rabbit\Config\PublishConfig;

interface PublisherInterface
{
    /**
     * @param PublishConfig $publishConfig
     * @return void
     */
    public function publishMessage(PublishConfig $publishConfig): void;
}
