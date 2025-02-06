<?php

declare(strict_types=1);

namespace SdkEsb\Rabbit;

use PhpAmqpLib\Exception\AMQPChannelClosedException;
use PhpAmqpLib\Exception\AMQPConnectionBlockedException;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SdkEsb\EventInterface;
use SdkEsb\PublisherInterface;
use SdkEsb\Rabbit\Config\PublisherConfig;
use SdkEsb\Rabbit\Exceptions\FailedToSetupDeadLetterQueueException;
use SdkEsb\Rabbit\Exceptions\SendPublishMessageException;

class Publisher implements PublisherInterface
{
    /** @var Connection */
    private $connection;

    /** @var RabbitManager */
    private $queueManager;

    /** @var LoggerInterface|NullLogger */
    private $logger;

    /** @var PublisherConfig */
    private $publisherConfig;

    public function __construct(Connection $connection, RabbitManager $queueManager, LoggerInterface $logger = null)
    {
        $this->connection = $connection;
        $this->queueManager = $queueManager;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Отправка сообщения
     *
     * @param EventInterface $event
     * @return void
     * @throws FailedToSetupDeadLetterQueueException
     * @throws SendPublishMessageException
     */
    public function publishMessage(EventInterface $event): void
    {
        $this->queueManager->setupDeadLetter($event);

        $message = $this->_makeMessage($event);
        $publisherConfig = $this->getPublisherConfig();

        try {
            $this->connection->getChannel()->basic_publish(
                new AMQPMessage($message),
                'exchange',
                $event->getEventName(),
                $publisherConfig->getMandatory(),
                $publisherConfig->getImmediate(),
                $publisherConfig->getTicket()
            );

            $this->logger->info('Сообщение опубликовано', [
                'direction' => 'publish',
                'message' => $message,
                'key' => $event->getKey(),
                'routing_key' => $event->getEventName(),
            ]);
        } catch (AMQPChannelClosedException|AMQPConnectionClosedException|AMQPConnectionBlockedException $e) {
            $messageError = 'Не удалось опубликовать сообщение';

            $this->logger->error($messageError, [
                'direction' => 'publish',
                'message' => $message,
                'key' => $event->getKey(),
                'routing_key' => $event->getEventName(),
            ]);

            throw new SendPublishMessageException($messageError . $e->getMessage());
        }
    }

    /**
     * @param EventInterface $event
     * @return string
     */
    private function _makeMessage(EventInterface $event): string
    {
        return json_encode([
            'eventName' => $event->getEventName(),
            'key' => $event->getKey(),
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $event->getData(),
        ]);
    }

    public function setConfig(PublisherConfig $publishConfig): Publisher
    {
        $this->publisherConfig = $publishConfig;
        return $this;
    }

    private function getPublisherConfig(): PublisherConfig
    {
        if ($this->publisherConfig === null) {
            return new PublisherConfig();
        }

        return $this->publisherConfig;
    }
}