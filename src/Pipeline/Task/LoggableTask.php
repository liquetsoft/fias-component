<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Psr\Log\LoggerInterface;

/**
 * Интерфейс для объекта операции, которая может записывать в лог свой действия.
 */
interface LoggableTask
{
    /**
     * Добавляет ссылку на объект для записи логов в объект операции.
     *
     * @param LoggerInterface $logger
     * @param array           $defaultContext
     */
    public function injectLogger(LoggerInterface $logger, array $defaultContext = []): void;

    /**
     * Записывает сообщение в лог.
     *
     * @param string $logLevel
     * @param string $message
     * @param array  $context
     */
    public function log(string $logLevel, string $message, array $context = []): void;
}
