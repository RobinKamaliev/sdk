<?php

declare(strict_types=1);

namespace SdkEsb\Rabbit\Config;

class Config
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var callable|null
     */
    private $customPublisher;

    /**
     * @var callable|null
     */
    private $customConsume;

    /**
     * @var string
     */
    private $exchange;

    public function __construct(
        string $host,
        int $port,
        string $user,
        string $password,
        callable $customPublisher = null,
        callable $customConsume = null
    )
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->customPublisher = $customPublisher;
        $this->customConsume = $customConsume;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return callable|null
     */
    public function getCustomPublisher(): ?callable
    {
        return $this->customPublisher;
    }

    /**
     * @return callable|null
     */
    public function getCustomConsume(): ?callable
    {
        return $this->customConsume;
    }
}