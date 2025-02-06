# SDK ESB


## Установка

```shell
composer require ...
```

# `Использование`

### Инициализация в `Provider.php`

```php
$config = new \SdkEsb\Rabbit\Config\Config($host, $port, $user, $password);
$sdk = new Sdk($config);
$sdk->init();
```
### Прокинуть в DI
```php
$sdk->getPublisher(), \SdkEsb\PublisherInterface::class;
$sdk->getConsume(), \SdkEsb\ConsumeInterface::class;
```
### Установка дополнительных параметров для отправителя и слушателя, при необходимости
```php
$publish->setConfig(new \SdkEsb\Rabbit\Config\PublisherConfig());
$consume->setConfig(new \SdkEsb\Rabbit\Config\ConsumeConfig());
```
### Отправить
```php
<?php

class RegistrationEvent implements \SdkEsb\EventInterface
{
    private $userId;
    private $data;

    public function __construct($userId, $data)
    {
        $this->userId = $userId;
        $this->data = $data;
    }

    public function getEventName(): string
    {
        return 'registration.user';
    }

    /** @return mixed */
    public function getKey()
    {
        return $this->userId;
    }

    /** @return mixed */
    public function getData()
    {
        return $this->data;
    }
}

class UseCase
{
    private $publisher;

    public function __construct(\SdkEsb\PublisherInterface $publisher)
    {
        $this->publisher = $publisher;
    }

    public function send()
    {
        $this->publisher->publishMessage(new RegistrationEvent());
    }
}
```
### Слушать
```php
<?php

class RegistrationHandler implements \SdkEsb\HandlerInterface
{
    public function execute(string $message): void
    {
        .. $message; //обработка сообщения
    }
}

class UseCase
{
    private $consumer;

    public function __construct(\SdkEsb\ConsumerInterface $consumer)
    {
        $this->consumer = $consumer;
    }

    public function send()
    {
        $this->consumer->consumeMessage('registration.user', new RegistrationHandler());
    }
}
```