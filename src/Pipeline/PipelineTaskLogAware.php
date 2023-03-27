<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline;

use Psr\Log\LoggerInterface;

/**
 * Интерфейс для объекта операции, в которую можно передать объект логгера.
 */
interface PipelineTaskLogAware extends PipelineTask
{
    /**
     * Добавляет ссылку на объект для записи логов в объект операции.
     */
    public function injectLogger(LoggerInterface $logger, array $defaultContext = []): void;
}
