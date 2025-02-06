<?php

declare(strict_types=1);

namespace SdkEsb\Rabbit;

use PhpAmqpLib\Exception\AMQPTimeoutException;
use SdkEsb\EventInterface;
use SdkEsb\Rabbit\Exceptions\FailedDeclareQueueException;
use SdkEsb\Rabbit\Exceptions\FailedToSetupDeadLetterQueueException;
use SdkEsb\Rabbit\Exceptions\QueueBindException;
use SdkEsb\Rabbit\Exceptions\RabbitMQException;

class RabbitManager
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Создает очередь, если она не существует.
     *
     * @param string $queue
     * @throws FailedDeclareQueueException
     */
    public function declareQueue(string $queue): void
    {
        try {
            $this->connection->getChannel()->queue_declare($queue, false, false, false, false);
        } catch (\Exception $e) {
            throw new FailedDeclareQueueException('Не удалось создать очередь: ' . $e->getMessage());
        }
    }

    /**
     * Создаем Dead Letter.
     *
     * @param EventInterface $event
     * @param string $type
     * @throws FailedToSetupDeadLetterQueueException
     */
    public function setupDeadLetter(
        EventInterface $event,
        string $type = 'topic'
    ): void {
        try {
            $deadLetterExchange = 'dead_letter_exchange.' . $event->getEventName();
            $deadLetterQueue = 'dead_letter_queue.' . $event->getEventName();
            $deadLetterRoutingKey = 'dead_routing_key.' . $event->getEventName();

            $this->connection->getChannel()->exchange_declare(
                $deadLetterExchange,
                $type,
                false,
                true,
                false
            );

            $this->connection->getChannel()->queue_declare(
                $deadLetterQueue,
                false,
                true,
                false,
                false,
                false,
                ['x-dead-letter-exchange' => ['S', 'exchange']]
            );

            $this->queueBind($deadLetterQueue, $deadLetterRoutingKey, $deadLetterExchange);
        } catch (\Exception $e) {
            throw new FailedToSetupDeadLetterQueueException(
                'Не удалось настроить очередь неотправленных писем: ' . $e->getMessage()
            );
        }
    }

    /**
     * Связываем queue с exchange по routingKey
     *
     * @param string $queue
     * @param string $exchange
     * @param string $routingKey
     * @return void
     * @throws QueueBindException
     */
    public function queueBind(string $queue, string $routingKey, string $exchange = 'exchange'): void
    {
        try {
            $this->connection->getChannel()->queue_bind($queue, $exchange, $routingKey);
        } catch (AMQPTimeoutException $e) {
            throw new QueueBindException(
                'Ошибка при связывании queue: ' . $queue . ' с exchange: ' . $exchange . ' по routingKey: ' . $routingKey
            );
        }
    }
}
