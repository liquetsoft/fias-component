<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Psr\Log\LoggerInterface;

/**
 * Реализация LoggableTask интерфейса.
 */
trait LoggableTaskTrait
{
    private ?LoggerInterface $logger = null;

    private array $defaultContext = [];

    /**
     * Добавляет ссылку на объект для записи логов в объект операции.
     */
    public function injectLogger(LoggerInterface $logger, array $defaultContext = []): void
    {
        $this->logger = $logger;
        $this->defaultContext = $defaultContext;
    }

    /**
     * Записывает сообщение в лог.
     */
    public function log(string $logLevel, string $message, array $context = []): void
    {
        if ($this->logger) {
            $context = array_merge($this->defaultContext, $context);
            $this->logger->log($logLevel, $message, $context);
        }
    }
}
