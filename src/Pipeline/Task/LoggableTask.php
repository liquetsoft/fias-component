<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Psr\Log\LoggerInterface;

/**
 * Интерфейс для объекта операции, которая может логгировать свой действия.
 */
interface LoggableTask
{
    /**
     * Добавляет ссылку на объект логгера в объект операции.
     *
     * @param LoggerInterface $logger
     * @param array           $defaultContext
     */
    public function injectLogger(LoggerInterface $logger, array $defaultContext = []): void;
}
