<?php

declare(strict_types=1);

namespace SdkEsb\Rabbit\Config;

class ConsumeConfig
{
    /**
     * @var string
     */
    private $consumerTag;

    /**
     * @var bool
     */
    private $noLocal;

    /**
     * @var bool
     */
    private $noAck;

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
        string $consumerTag = '',
        bool $noLocal = false,
        bool $noAck = false,
        bool $exclusive = false,
        bool $nowait = false,
        ?bool $ticket = null,
        array $arguments = array()
    ) {
        $this->consumerTag = $consumerTag;
        $this->noLocal = $noLocal;
        $this->noAck = $noAck;
        $this->exclusive = $exclusive;
        $this->nowait = $nowait;
        $this->ticket = $ticket;
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function getConsumerTag(): string
    {
        return $this->consumerTag;
    }

    /**
     * @return bool
     */
    public function getNoLocal(): bool
    {
        return $this->noLocal;
    }

    /**
     * @return bool
     */
    public function getNoAck(): bool
    {
        return $this->noAck;
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