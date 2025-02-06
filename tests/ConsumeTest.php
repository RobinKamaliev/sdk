<?php

declare(strict_types=1);

namespace SdkEsb\Rabbit\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use SdkEsb\Rabbit\Consumer;
use SdkEsb\Rabbit\Connection;
use SdkEsb\Rabbit\RabbitManager;
use SdkEsb\HandlerInterface;
use SdkEsb\Rabbit\Config\ConsumeConfig;
use SdkEsb\Rabbit\Exceptions\FailedConsumeMessageException;
use SdkEsb\Rabbit\Exceptions\FailedDeclareQueueException;

class ConsumerTest extends TestCase
{
    /** @var Connection */
    private $mockConnection;

    /** @var AMQPChannel */
    private $mockChannel;

    /** @var RabbitManager */
    private $mockRabbitManager;

    /** @var LoggerInterface */
    private $mockLogger;

    /** @var HandlerInterface */
    private $mockHandler;

    /** @var AMQPMessage */
    private $mockMessage;

    protected function setUp(): void
    {
        $this->mockConnection = $this->createMock(Connection::class);
        $this->mockChannel = $this->createMock(AMQPChannel::class);
        $this->mockRabbitManager = $this->createMock(RabbitManager::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        $this->mockHandler = $this->createMock(HandlerInterface::class);
        $this->mockMessage = $this->createMock(AMQPMessage::class);

        $this->mockConnection
            ->method('getChannel')
            ->willReturn($this->mockChannel);
    }

    public function testConsumeMessageSuccess()
    {
        $queueName = 'test.queue';
        $messageBody = 'test message';

        $this->mockRabbitManager
            ->expects($this->once())
            ->method('declareQueue')
            ->with($queueName);

        $this->mockMessage->body = $messageBody;

        $this->mockHandler
            ->expects($this->once())
            ->method('execute')
            ->with($messageBody);

        $this->mockMessage
            ->expects($this->once())
            ->method('ack');

        $this->mockLogger
            ->expects($this->once())
            ->method('info')
            ->with($this->stringContains('Сообщение обработано'));

        $callback = null;

        $this->mockChannel
            ->method('basic_consume')
            ->willReturnCallback(function ($queue, $consumerTag, $noLocal, $noAck, $exclusive, $nowait, $cb) use (&$callback) {
                $callback = $cb;
            });

        $this->mockChannel
            ->method('is_consuming')
            ->willReturnOnConsecutiveCalls(true, false);

        $this->mockChannel
            ->method('wait')
            ->willReturnCallback(function () use (&$callback) {
                $callback($this->mockMessage);
            });

        $consumer = new Consumer($this->mockConnection, $this->mockRabbitManager, $this->mockLogger);
        $consumer->consumeMessage($queueName, $this->mockHandler);
    }

    public function testConsumeMessageHandlerThrowsException()
    {
        $queueName = 'test.queue';

        $this->mockRabbitManager
            ->expects($this->once())
            ->method('declareQueue')
            ->with($queueName);

        $this->mockMessage->body = 'test message';

        $this->mockHandler
            ->method('execute')
            ->willThrowException(new \Exception('Handler error'));

        $this->mockMessage
            ->expects($this->once())
            ->method('nack')
            ->with(true);

        $this->mockLogger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Сообщение вернулось в очередь'));

        $callback = null;

        $this->mockChannel
            ->method('basic_consume')
            ->willReturnCallback(function ($queue, $consumerTag, $noLocal, $noAck, $exclusive, $nowait, $cb) use (&$callback) {
                $callback = $cb;
            });

        $this->mockChannel
            ->method('is_consuming')
            ->willReturnOnConsecutiveCalls(true, false);

        $this->mockChannel
            ->method('wait')
            ->willReturnCallback(function () use (&$callback) {
                $callback($this->mockMessage);
            });

        $consumer = new Consumer($this->mockConnection, $this->mockRabbitManager, $this->mockLogger);
        $consumer->consumeMessage($queueName, $this->mockHandler);
    }

    public function testConsumeMessageThrowsException()
    {
        $queueName = 'test.queue';

        $this->mockRabbitManager
            ->expects($this->once())
            ->method('declareQueue')
            ->with($queueName);

        $this->mockChannel
            ->method('basic_consume')
            ->willThrowException(new \Exception('Queue error'));

        $this->mockLogger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Не удалось обработать сообщение'));

        $this->expectException(FailedConsumeMessageException::class);

        $consumer = new Consumer($this->mockConnection, $this->mockRabbitManager, $this->mockLogger);
        $consumer->consumeMessage($queueName, $this->mockHandler);
    }
}
