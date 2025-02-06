<?php

declare(strict_types=1);

namespace SdkEsb\Rabbit\Tests;

use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPChannelClosedException;
use SdkEsb\Rabbit\Publisher;
use SdkEsb\Rabbit\Connection;
use SdkEsb\Rabbit\RabbitManager;
use SdkEsb\EventInterface;
use SdkEsb\Rabbit\Exceptions\SendPublishMessageException;
use SdkEsb\Rabbit\Config\PublisherConfig;

class PublisherTest extends TestCase
{
    /** @var Connection */
    private $mockConnection;

    /** @var AMQPChannel */
    private $mockChannel;

    /** @var RabbitManager */
    private $mockRabbitManager;

    /** @var LoggerInterface */
    private $mockLogger;

    /** @var EventInterface */
    private $mockEvent;

    protected function setUp(): void
    {
        $this->mockConnection = $this->createMock(Connection::class);
        $this->mockChannel = $this->createMock(AMQPChannel::class);
        $this->mockRabbitManager = $this->createMock(RabbitManager::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        $this->mockEvent = $this->createMock(EventInterface::class);

        $this->mockConnection
            ->method('getChannel')
            ->willReturn($this->mockChannel);
    }

    public function testPublishMessageSuccess(): void
    {
        $this->mockEvent->method('getEventName')->willReturn('test.event');
        $this->mockEvent->method('getKey')->willReturn('test-key');
        $this->mockEvent->method('getData')->willReturn(['test' => 'test']);
        $this->mockChannel
            ->expects($this->once())
            ->method('basic_publish')
            ->with($this->isInstanceOf(AMQPMessage::class), 'exchange', 'test.event');

        $this->mockLogger
            ->expects($this->once())
            ->method('info')
            ->with('Сообщение опубликовано');

        $publisher = new Publisher($this->mockConnection, $this->mockRabbitManager, $this->mockLogger);
        $publisher->publishMessage($this->mockEvent);
    }

    public function testPublishMessageThrowsException(): void
    {
        $this->mockEvent->method('getEventName')->willReturn('test.event');
        $this->mockEvent->method('getKey')->willReturn('test-key');
        $this->mockEvent->method('getData')->willReturn(['test' => 'test']);

        $this->mockChannel
            ->method('basic_publish')
            ->willThrowException(new AMQPChannelClosedException());

        $this->mockLogger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Не удалось опубликовать сообщение'));

        $this->expectException(SendPublishMessageException::class);

        $publisher = new Publisher($this->mockConnection, $this->mockRabbitManager, $this->mockLogger);
        $publisher->publishMessage($this->mockEvent);
    }
}
