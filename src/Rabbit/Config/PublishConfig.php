<?php

declare(strict_types=1);

namespace SdkEsb\Rabbit\Config;

class PublishConfig
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $exchange;

    /**
     * @var string
     */
    private $routingKey;

    /**
     * @var bool
     */
    private $mandatory;

    /**
     * @var bool
     */
    private $immediate;

    /**
     * @var int|null
     */
    private $ticket;

    public function __construct(
        string $message,
        string $exchange,
        string $routingKey,
        bool $mandatory = false,
        bool $immediate = false,
        ?int $ticket = null
    )
    {
        $this->message = $message;
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
        $this->mandatory = $mandatory;
        $this->immediate = $immediate;
        $this->ticket = $ticket;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getExchange(): string
    {
        return $this->exchange;
    }

    /**
     * @return string
     */
    public function getRoutingKey(): string
    {
        return $this->routingKey;
    }

    /**
     * @return bool
     */
    public function getMandatory(): bool
    {
        return $this->mandatory;
    }

    /**
     * @return bool
     */
    public function getImmediate(): bool
    {
        return $this->immediate;
    }

    /**
     * @return int|null
     */
    public function getTicket(): ?int
    {
        return $this->ticket;
    }
}