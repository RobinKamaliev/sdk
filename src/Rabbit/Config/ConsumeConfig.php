<?php

declare(strict_types=1);

namespace SdkEsb\Rabbit\Config;

class ConsumeConfig
{
    /**
     * @var string
     */
    private $queue;

    /**
     * @var callable|null
     */
    private $callback;

    /**
     * @var string
     */
    private $consumer_tag;

    /**
     * @var bool
     */
    private $no_local;

    /**
     * @var bool
     */
    private $no_ack;

    /**
     * @var bool
     */
    private $exclusive;

    /**
     * @var bool
     */
    private $nowait;

    /**
     * @var bool|null
     */
    private $ticket;

    /**
     * @var array
     */
    private $arguments;

    public function __construct(
        string $queue,
        callable $callback = null,
        string $consumer_tag = '',
        bool $no_local = false,
        bool $no_ack = false,
        bool $exclusive = false,
        bool $nowait = false,
        ?bool $ticket = null,
        array $arguments = array()
    ) {
        $this->queue = $queue;
        $this->callback = $callback;
        $this->consumer_tag = $consumer_tag;
        $this->no_local = $no_local;
        $this->no_ack = $no_ack;
        $this->exclusive = $exclusive;
        $this->nowait = $nowait;
        $this->ticket = $ticket;
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function getQueue(): string
    {
        return $this->queue;
    }

    /**
     * @return callable|null
     */
    public function getCallback(): ?callable
    {
        return $this->callback;
    }

    /**
     * @return string
     */
    public function getConsumerTag(): string
    {
        return $this->consumer_tag;
    }

    /**
     * @return bool
     */
    public function getNoLocal(): bool
    {
        return $this->no_local;
    }

    /**
     * @return bool
     */
    public function getNoAck(): bool
    {
        return $this->no_ack;
    }

    /**
     * @return bool
     */
    public function getExclusive(): bool
    {
        return $this->exclusive;
    }

    /**
     * @return bool|null
     */
    public function getNowait(): ?bool
    {
        return $this->nowait;
    }

    /**
     * @return bool|null
     */
    public function getTicket(): ?bool
    {
        return $this->ticket;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}