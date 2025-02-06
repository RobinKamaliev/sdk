<?php

declare(strict_types=1);

namespace SdkEsb\Rabbit\Tests;

use PhpAmqpLib\Exception\AMQPTimeoutException;
use PHPUnit\Framework\TestCase;
use SdkEsb\Rabbit\Connection;
use SdkEsb\Rabbit\RabbitManager;
use SdkEsb\Rabbit\Exceptions\FailedDeclareQueueException;
use SdkEsb\Rabbit\Exceptions\FailedToSetupDeadLetterQueueException;
use SdkEsb\Rabbit\Exceptions\QueueBindException;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use SdkEsb\EventInterface;
use PHPUnit\Framework\MockObject\MockObject;

class RabbitManagerTest extends TestCase
{
    /** @var RabbitManager */
    private $rabbitManager;

    /** @var AMQPChannel */
    private $mockChannel;

    protected function setUp(): void
    {
        $mockConnection = $this->createMock(Connection::class);
        $this->mockChannel = $this->createMock(AMQPChannel::class);
        $mockConnection->method('getChannel')->willReturn($this->mockChannel);
        $this->rabbitManager = new RabbitManager($mockConnection);
    }

    public function testDeclareQueueShouldCallQueueDeclare(): void
    {
        $this->mockChannel
            ->expects($this->once())
            ->method('queue_declare')
            ->with('testQueue', false, false, false, false);

        $this->rabbitManager->declareQueue('testQueue');
    }

    public function testDeclareQueueShouldThrowFailedDeclareQueueExceptionOnError(): void
    {
        $this->mockChannel
            ->method('queue_declare')
            ->will($this->throwException(new \Exception('Some error')));

        $this->expectException(FailedDeclareQueueException::class);
        $this->expectExceptionMessage('Не удалось создать очередь: Some error');

        $this->rabbitManager->declareQueue('testQueue');
    }
    public function testSetupDeadLetterShouldCallExchangeDeclareAndQueueDeclare(): void
    {
        $eventMock = $this->createMock(EventInterface::class);
        $eventMock->method('getEventName')->willReturn('testEvent');

        $this->mockChannel
            ->expects($this->at(0))
            ->method('exchange_declare')
            ->with('dead_letter_exchange.testEvent', 'topic', false, true, false);

        $this->mockChannel
            ->expects($this->at(1))
            ->method('queue_declare')
            ->with(
                'dead_letter_queue.testEvent',
                false,
                true,
                false,
                false,
                false,
                ['x-dead-letter-exchange' => ['S', 'exchange']]
            );

        $this->mockChannel
            ->expects($this->once())
            ->method('queue_bind')
            ->with('dead_letter_queue.testEvent', 'dead_letter_exchange.testEvent', 'dead_routing_key.testEvent');

        $this->rabbitManager->setupDeadLetter($eventMock);
    }


    public function testSetupDeadLetterShouldThrowFailedToSetupDeadLetterQueueExceptionOnError(): void
    {
        $eventMock = $this->createMock(EventInterface::class);
        $eventMock->method('getEventName')->willReturn('testEvent');

        $this->mockChannel
            ->method('exchange_declare')
            ->will($this->throwException(new \Exception('Some error')));

        $this->expectException(FailedToSetupDeadLetterQueueException::class);
        $this->expectExceptionMessage('Не удалось настроить очередь неотправленных писем: Some error');

        $this->rabbitManager->setupDeadLetter($eventMock);
    }

    public function testQueueBindShouldCallQueueBind(): void
    {
        $this->mockChannel
            ->expects($this->once())
            ->method('queue_bind')
            ->with('testQueue', 'exchange', 'testRoutingKey');

        $this->rabbitManager->queueBind('testQueue', 'testRoutingKey');
    }

    public function testQueueBindShouldThrowQueueBindExceptionOnError(): void
    {
        $this->mockChannel
            ->method('queue_bind')
            ->will($this->throwException(new AMQPTimeoutException('Some error')));

        $this->expectException(QueueBindException::class);
        $this->expectExceptionMessage('Ошибка при связывании queue: testQueue с exchange: exchange по routingKey: testRoutingKey');

        $this->rabbitManager->queueBind('testQueue', 'testRoutingKey');
    }
}
