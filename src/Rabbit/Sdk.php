<?php

declare(strict_types=1);

namespace SdkEsb\Rabbit;

use Exception;
use SdkEsb\ConsumerInterface;
use SdkEsb\PublisherInterface;
use SdkEsb\Rabbit\Config\Config;
use SdkEsb\Rabbit\Exceptions\RabbitMQException;

class Sdk
{
    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var ConsumerInterface
     */
    private $consume;

    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Инициализация.
     *
     * @throws RabbitMQException
     */
    public function init(): void
    {
        $connection = new Connection($this->config);
        $rabbitManager = new RabbitManager($connection);

        $this->publisher = $this->config->getCustomPublisher() ?? new Publisher($connection, $rabbitManager);
        $this->consume = $this->config->getCustomConsume() ?? new Consumer($connection, $rabbitManager);
    }

    public function getPublisher(): PublisherInterface
    {
        if ($this->publisher === null) {
            throw new Exception("Паблишер не установлен или sdk не заиничено");
        }
        return $this->publisher;
    }

    public function getConsume(): ConsumerInterface
    {
        if ($this->consume === null) {
            throw new Exception("Консюмер не установлен или sdk не заиничено");
        }
        return $this->consume;
    }
}