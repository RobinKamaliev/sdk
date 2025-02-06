<?php

namespace SdkEsb\Rabbit\Tests;

use PHPUnit\Framework\TestCase;
use SdkEsb\Rabbit\Connection;
use PhpAmqpLib\Channel\AMQPChannel;
use SdkEsb\Rabbit\Config\Config;
use SdkEsb\Rabbit\Exceptions\RabbitMQException;

class ConnectionTest extends TestCase
{
    /** @var Connection */
    private $mockConnection;

    /** @var Config */
    private $mockConfig;

    protected function setUp(): void
    {
        $this->mockConfig = $this->createMock(Config::class);
        $this->mockConnection = $this->createMock(Connection::class);
    }

    public function testGetChannelInstanceOf(): void
    {
        $channel = $this->mockConnection->getChannel();
        $this->assertInstanceOf(AMQPChannel::class, $channel);
    }

    public function testChannelRabbitMQException(): void
    {
        $this->expectException(RabbitMQException::class);
        new Connection($this->mockConfig);
    }
}
