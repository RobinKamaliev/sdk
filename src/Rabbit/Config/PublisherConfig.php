<?php

declare(strict_types=1);

namespace SdkEsb\Rabbit\Config;

class PublisherConfig
{
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
        bool $mandatory = false,
        bool $immediate = false,
        ?int $ticket = null
    )
    {
        $this->mandatory = $mandatory;
        $this->immediate = $immediate;
        $this->ticket = $ticket;
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