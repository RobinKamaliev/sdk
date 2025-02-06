<?php

namespace SdkEsb\Rabbit\Tests;

use PHPUnit\Framework\TestCase;
use SdkEsb\Rabbit\Config\ConsumeConfig;

class ConsumeConfigTest extends TestCase
{
    public function testParametersFromConstructor(): void
    {
        $config = new ConsumeConfig('test');

        $this->assertSame('test', $config->getConsumerTag());
        $this->assertSame(false, $config->getNoLocal());
        $this->assertSame(false, $config->getNoAck());
        $this->assertSame(false, $config->getExclusive());
        $this->assertSame(false, $config->getNowait());
        $this->assertSame(null, $config->getTicket());
        $this->assertSame(array(), $config->getArguments());
    }
}