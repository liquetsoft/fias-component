<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Трэйт для объекта операции, в которую можно передать объект логгера.
 */
trait PipelineTaskLogAwareTrait
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

    /**
     * Записывает сообщение в лог с уровнем INFO.
     */
    public function logInfo(string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }
}
