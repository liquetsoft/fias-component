<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Psr\Log\LoggerInterface;

/**
 * Реализация LoggableTask интерфейса.
 */
trait LoggableTaskTrait
{
    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * @var array
     */
    protected $defaultContext = [];

    /**
     * Добавляет ссылку на объект для записи логов в объект операции.
     *
     * @param LoggerInterface $logger
     * @param array           $defaultContext
     */
    public function injectLogger(LoggerInterface $logger, array $defaultContext = []): void
    {
        $this->logger = $logger;
        $this->defaultContext = $defaultContext;
    }

    /**
     * Записывает сообщение в лог.
     *
     * @param string $logLevel
     * @param string $message
     * @param array  $context
     */
    public function log(string $logLevel, string $message, array $context = []): void
    {
        if ($this->logger) {
            $context = array_merge($this->defaultContext, $context);
            $this->logger->log($logLevel, $message, $context);
        }
    }
}
