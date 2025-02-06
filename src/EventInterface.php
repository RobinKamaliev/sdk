<?php

declare(strict_types=1);

namespace SdkEsb;

interface EventInterface
{
    /**
     * Используется для определения routing-key и очереди.
     *
     * @return string
     */
    public function getEventName(): string;

    /**
     * Получение уникальных данных.
     *
     * @return mixed
     */
    public function getKey();

    /**
     * Получения данных сообщения.
     *
     * @return mixed
     */
    public function getData();
}