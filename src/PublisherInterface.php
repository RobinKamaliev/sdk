<?php

declare(strict_types=1);

namespace SdkEsb;

interface PublisherInterface
{
    /**
     * @param EventInterface $event
     * @return void
     */
    public function publishMessage(EventInterface $event): void;
}
