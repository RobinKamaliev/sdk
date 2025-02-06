<?php

namespace SdkEsb\Rabbit\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use SdkEsb\Rabbit\Sdk;
use SdkEsb\Rabbit\Config\Config;
use SdkEsb\Rabbit\Connection;
use SdkEsb\Rabbit\RabbitManager;
use SdkEsb\Rabbit\Publisher;
use SdkEsb\Rabbit\Consumer;

class SdkTest extends TestCase
{
    /** @var Config */
    private $mockConfig;

    /** @var Publisher */
    private $mockPublisher;

    /** @var Consumer */
    private $mockConsumer;

    /** @var Sdk */
    private $sdk;

    protected function setUp(): void
    {
        $this->mockConfig = $this->createMock(Config::class);
        $this->mockConnection = $this->createMock(Connection::class);
        $this->mockRabbitManager = $this->createMock(RabbitManager::class);
        $this->mockPublisher = $this->createMock(Publisher::class);
        $this->mockConsumer = $this->createMock(Consumer::class);

        $this->mockConfig->method('getCustomPublisher')->willReturn(null);
        $this->mockConfig->method('getCustomConsume')->willReturn(null);

        $this->sdk = $this->getMockBuilder(Sdk::class)->setConstructorArgs([$this->mockConfig])->setMethods(['init'])->getMock();
    }

    public function testGetPublisherAndConsumerInstanceOf(): void
    {
        $reflection = new \ReflectionClass(Sdk::class);
        $publisherPropertyPublisher = $reflection->getProperty('publisher');
        $publisherPropertyPublisher->setAccessible(true);
        $publisherPropertyPublisher->setValue($this->sdk, $this->mockPublisher);
        $consumeProperty = $reflection->getProperty('consume');
        $consumeProperty->setAccessible(true);
        $consumeProperty->setValue($this->sdk, $this->mockConsumer);

        $this->assertInstanceOf(Publisher::class, $this->sdk->getPublisher());
        $this->assertInstanceOf(Consumer::class, $this->sdk->getConsume());
    }

    public function testGetPublisherThrowsExceptionIfNotInitialized(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Паблишер не установлен или sdk не заиничено");

        $this->sdk->getPublisher();
    }

    public function testGetConsumeThrowsExceptionIfNotInitialized(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Консюмер не установлен или sdk не заиничено");

        $this->sdk->getConsume();
    }

    public function testInitWithCustomPublisherAndConsumer(): void
    {
        $reflection = new \ReflectionClass(Sdk::class);
        $publisherPropertyPublisher = $reflection->getProperty('publisher');
        $publisherPropertyPublisher->setAccessible(true);
        $publisherPropertyPublisher->setValue($this->sdk, $this->mockPublisher);
        $consumeProperty = $reflection->getProperty('consume');
        $consumeProperty->setAccessible(true);
        $consumeProperty->setValue($this->sdk, $this->mockConsumer);
        $this->mockConfig->method('getCustomPublisher')->willReturn($this->mockPublisher);
        $this->mockConfig->method('getCustomConsume')->willReturn($this->mockConsumer);

        $this->assertSame($this->mockPublisher, $this->sdk->getPublisher());
        $this->assertSame($this->mockConsumer, $this->sdk->getConsume());
    }
}
