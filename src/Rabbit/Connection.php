<?php

declare(strict_types=1);

namespace SdkEsb\Rabbit;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use SdkEsb\Rabbit\Config\Config;
use SdkEsb\Rabbit\Exceptions\RabbitMQException;

class Connection
{
    /** @var AMQPStreamConnection */
    private $connection;

    /** @var AMQPChannel */
    private $channel;

    /**
     * Подключение к RabbitMq.
     *
     * @param Config $config
     * @throws RabbitMQException
     */
    public function __construct(Config $config)
    {
        try {
            $this->connection = new AMQPStreamConnection(
                $config->getHost(),
                $config->getPort(),
                $config->getUser(),
                $config->getPassword()
            );

            $this->channel = $this->connection->channel();
        } catch (\Exception $e) {
            throw new RabbitMQException('Connection failed: ' . $e->getMessage());
        }
    }

    /**
     * @return AMQPChannel
     */
    public function getChannel(): AMQPChannel
    {
        if (!$this->channel->is_open()) {
            $this->channel = $this->connection->channel();
        }

        return $this->channel;
    }
}
