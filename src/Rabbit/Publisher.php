<?php

declare(strict_types=1);

namespace SdkEsb\Rabbit;

use PhpAmqpLib\Exception\AMQPChannelClosedException;
use PhpAmqpLib\Exception\AMQPConnectionBlockedException;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SdkEsb\PublisherInterface;
use SdkEsb\Rabbit\Config\PublishConfig;
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

    public function __construct(Connection $connection, RabbitManager $queueManager, LoggerInterface $logger = null)
    {
        $this->connection = $connection;
        $this->queueManager = $queueManager;
        $this->logger = $logger ?: new NullLogger();
    }


    /**
     * Отправка сообщения
     *
     * @param PublishConfig $publishConfig
     * @return void
     * @throws FailedToSetupDeadLetterQueueException
     * @throws SendPublishMessageException
     */
    public function publishMessage(PublishConfig $publishConfig): void
    {
        $this->queueManager->setupDeadLetter(
            $publishConfig->getExchange()
        );

        try {
            $msg = new AMQPMessage($publishConfig->getMessage());
            $this->connection->getChannel()->basic_publish(
                $msg,
                $publishConfig->getExchange(),
                $publishConfig->getRoutingKey(),
                $publishConfig->getMandatory(),
                $publishConfig->getImmediate(),
                $publishConfig->getTicket()
            );

            $this->logger->info('Сообщение опубликовано', [
                'direction' => 'publish',
                'message' => $publishConfig->getMessage(),
                'exchange' => $publishConfig->getExchange(),
                'routing_key' => $publishConfig->getRoutingKey(),
            ]);
        } catch (AMQPChannelClosedException|AMQPConnectionClosedException|AMQPConnectionBlockedException $e) {
            $messageError = 'Не удалось опубликовать сообщение';

            $this->logger->error($messageError, [
                'direction' => 'publish',
                'message' => $e->getMessage(),
                'exchange' => $publishConfig->getExchange(),
                'routing_key' => $publishConfig->getRoutingKey(),
            ]);

            throw new SendPublishMessageException($messageError . $e->getMessage());
        }
    }
}
