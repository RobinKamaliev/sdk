<?php

declare(strict_types=1);

namespace SdkEsb\Rabbit;

use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SdkEsb\HandlerInterface;
use SdkEsb\ConsumerInterface;
use SdkEsb\Rabbit\Config\ConsumeConfig;
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

    /** @var ConsumeConfig */
    private $consumeConfig = null;

    public function __construct(Connection $connection, RabbitManager $queueManager, LoggerInterface $logger = null)
    {
        $this->connection = $connection;
        $this->queueManager = $queueManager;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Слушатель сообщений.
     *
     * @param string $queue
     * @param HandlerInterface $handler
     * @return void
     * @throws FailedConsumeMessageException
     * @throws FailedDeclareQueueException
     */
    public function consumeMessage(string $queue, HandlerInterface $handler): void
    {
        $this->_validateQueue($queue);
        $consumeConfig = $this->getConsumeConfig();

        try {
            $channel = $this->connection->getChannel();
            $channel->basic_consume(
                $queue,
                $consumeConfig->getConsumerTag(),
                $consumeConfig->getNoLocal(),
                $consumeConfig->getNoAck(),
                $consumeConfig->getExclusive(),
                $consumeConfig->getNowait(),
                function (AMQPMessage $msg) use ($queue, $handler): void {
                    try {
                        $handler->execute($msg->body);
                        $msg->ack();

                        $this->logger->info('Сообщение обработано', [
                            'direction' => 'consume',
                            'message' => $msg->body,
                            'queue' => $queue,
                        ]);
                    } catch (\Exception $e) {
                        $msg->nack(true);

                        $this->logger->error('Сообщение вернулось в очередь', [
                            'direction' => 'consume',
                            'message' => $msg->body,
                            'queue' => $queue,
                        ]);
                    }
                },
                $consumeConfig->getTicket(),
                $consumeConfig->getArguments()
            );

            while ($channel->is_consuming()) {
                $channel->wait();
            }
        } catch (\Exception $e) {
            $messageError = 'Не удалось обработать сообщение';

            $this->logger->error($messageError, [
                'direction' => 'consume',
                'message' => $e->getMessage(),
                'queue' => $queue,
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

    public function setConfig(ConsumeConfig $consumeConfig): Consumer
    {
        $this->consumeConfig = $consumeConfig;
        return $this;
    }

    private function getConsumeConfig(): ConsumeConfig
    {
        if ($this->consumeConfig === null) {
            return new ConsumeConfig();
        }

        return $this->consumeConfig;
    }
}
