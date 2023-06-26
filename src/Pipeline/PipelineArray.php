<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline;

use Liquetsoft\Fias\Component\Exception\PipelineException;
use Liquetsoft\Fias\Component\Helper\IdHelper;
use Liquetsoft\Fias\Component\Helper\StringHelper;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Объект, который содержит внутренний массив со списком операций для исполнения.
 */
final class PipelineArray implements Pipeline
{
    private readonly string $id;

    public function __construct(
        /** @var iterable<PipelineTask> */
        private readonly iterable $tasks,
        private readonly ?PipelineTask $cleanupTask = null,
        private readonly ?LoggerInterface $logger = null,
        string $id = null
    ) {
        $this->id = StringHelper::normalize($id === null ? IdHelper::createUniqueId() : $id);
        if ($this->id === '') {
            throw PipelineException::create("Pipeline id can't be empty");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function run(PipelineState $state): void
    {
        $this->log('Pipeline started');

        foreach ($this->tasks as $task) {
            $taskName = $this->getTaskId($task);
            try {
                $state = $this->proceedTask($state, $task);
            } catch (\Throwable $e) {
                $this->log(
                    'Task throwed an error',
                    [
                        'task' => $taskName,
                        'error' => $e->getMessage(),
                    ]
                );
                $this->proceedCleanup($state);
                throw PipelineException::wrap($e);
            }
            if ($state->get(PipelineStateParam::INTERRUPT_PIPELINE) === true) {
                $this->log('Pipeline interrupted by task', ['task' => $taskName]);
                break;
            }
        }

        $this->proceedCleanup($state);
        $this->log('Pipeline completed');
    }

    /**
     * Запускает задачу на исполнение.
     *
     * @throws \Exception
     */
    private function proceedTask(PipelineState $state, PipelineTask $task): PipelineState
    {
        $taskName = $this->getTaskId($task);

        $this->log('Task started', ['task' => $taskName]);

        $this->injectLoggerToTask($task);
        $newState = $task->run($state);

        $this->log('Task completed', ['task' => $taskName]);

        return $newState;
    }

    /**
     * Обработка завершения задачи.
     *
     * @throws \Exception
     */
    private function proceedCleanup(PipelineState $state): void
    {
        if ($this->cleanupTask) {
            $this->log('Cleanup started');
            $this->proceedTask($state, $this->cleanupTask);
        } else {
            $this->log('Cleanup skipped');
        }
    }

    /**
     * Записывает в лог данные.
     */
    private function log(string $message, array $context = []): void
    {
        $this->logger?->log(
            LogLevel::INFO,
            $message,
            $this->createLoggerContext($context)
        );
    }

    /**
     * Добавляет объект для записи логов в операцию, если операция это поддерживает.
     */
    private function injectLoggerToTask(PipelineTask $task): void
    {
        if ($task instanceof PipelineTaskLogAware && $this->logger) {
            $task->injectLogger(
                $this->logger,
                $this->createLoggerContext(
                    [
                        'task' => $this->getTaskId($task),
                    ]
                )
            );
        }
    }

    /**
     * Возвращает контекст для записи логов по умолчанию.
     */
    private function createLoggerContext(array $currentContext = []): array
    {
        return array_merge(
            $currentContext,
            [
                'source' => IdHelper::getComponentId(),
                'pipeline_class' => self::class,
                'pipeline_id' => $this->id,
            ]
        );
    }

    /**
     * Возвращает идентификатор операции.
     */
    private function getTaskId(PipelineTask $task): string
    {
        return \get_class($task);
    }
}
