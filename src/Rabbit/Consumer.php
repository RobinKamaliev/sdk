<?php

declare(strict_types=1);

namespace SdkEsb\Rabbit;

use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SdkEsb\Rabbit\Config\ConsumeConfig;
use SdkEsb\ConsumerInterface;
use SdkEsb\Rabbit\Exceptions\FailedConsumeMessageException;
use SdkEsb\Rabbit\Exceptions\FailedDeclareQueueException;

class Consumer implements ConsumerInterface
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
     * Слушатель сообщений.
     *
     * @param ConsumeConfig $config
     * @return void
     * @throws FailedConsumeMessageException
     * @throws FailedDeclareQueueException
     */
    public function consumeMessage(ConsumeConfig $config): void
    {
        $this->_validateQueue($config->getQueue());

        try {
            $channel = $this->connection->getChannel();
            $channel->basic_consume(
                $config->getQueue(),
                $config->getConsumerTag(),
                $config->getNoLocal(),
                $config->getNoAck(),
                $config->getExclusive(),
                $config->getNowait(),
                function (AMQPMessage $msg) use ($config): void {
                    try {
                        $config->getCallback()($msg->body);
                        $msg->ack();

                        $this->logger->info('Сообщение обработано', [
                            'direction' => 'consume',
                            'message' => $msg->body,
                            'queue' => $config->getQueue(),
                        ]);
                    } catch (\Exception $e) {
                        $msg->nack(true);

                        $this->logger->error('Сообщение вернулось в очередь', [
                            'direction' => 'consume',
                            'message' => $msg->body,
                            'queue' => $config->getQueue(),
                        ]);
                    }
                },
                $config->getTicket(),
                $config->getArguments()
            );

            while ($channel->is_consuming()) {
                $channel->wait();
            }
        } catch (\Exception $e) {
            $messageError = 'Не удалось обработать сообщение';

            $this->logger->error($messageError, [
                'direction' => 'consume',
                'message' => $e->getMessage(),
                'queue' => $config->getQueue(),
            ]);

            throw new FailedConsumeMessageException($messageError . $e->getMessage());
        }
    }

    /**
     * @param string $queue
     * @return void
     * @throws FailedDeclareQueueException
     */
    private function _validateQueue(string $queue): void
    {
        $this->queueManager->declareQueue($queue);
    }
}
