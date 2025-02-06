<?php

namespace SdkEsb\Rabbit\Tests;

use PHPUnit\Framework\TestCase;
use SdkEsb\Rabbit\Config\PublisherConfig;

class PublisherConfigTest extends TestCase
{
    public function testParametersFromConstructor(): void
    {
        $config = new PublisherConfig(true);

        $this->assertSame(true, $config->getMandatory());
        $this->assertSame(false, $config->getImmediate());
        $this->assertSame(null, $config->getTicket());
    }
}