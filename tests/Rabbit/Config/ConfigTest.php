<?php

namespace SdkEsb\Rabbit\Tests;

use PHPUnit\Framework\TestCase;
use SdkEsb\Rabbit\Config\Config;

class ConfigTest extends TestCase
{
    public function testParametersFromConstructor(): void
    {
        $host = 'rabbitmq';
        $port = 5672;
        $user = 'guest';
        $passport = 'guest';
        $config = new Config($host, $port, $user, $passport);

        $this->assertSame($host, $config->getHost());
        $this->assertSame($port, $config->getPort());
        $this->assertSame($user, $config->getUser());
        $this->assertSame($passport, $config->getPassword());
        $this->assertSame(null, $config->getCustomPublisher());
        $this->assertSame(null, $config->getCustomConsume());
    }
}
