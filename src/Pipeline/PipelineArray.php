<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline;

use Liquetsoft\Fias\Component\Exception\PipelineException;
use Liquetsoft\Fias\Component\Helper\IdHelper;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Объект, который содержит внутренний массив со списком операций для исполнения.
 */
final class PipelineArray implements Pipeline
{
    private readonly string $id;

    /**
     * @var iterable<PipelineTask>
     */
    private readonly iterable $tasks;

    private readonly ?PipelineTask $cleanupTask;

    private readonly ?LoggerInterface $logger;

    /**
     * @param iterable<PipelineTask> $tasks
     */
    public function __construct(iterable $tasks, ?PipelineTask $cleanupTask = null, ?LoggerInterface $logger = null)
    {
        $this->id = IdHelper::createUniqueId();
        $this->tasks = $tasks;
        $this->cleanupTask = $cleanupTask;
        $this->logger = $logger;
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
                    'Task throwed an error: ' . $e->getMessage(),
                    [
                        'task' => $taskName,
                        'error' => $e->getMessage(),
                    ]
                );
                $this->proceedCleanup($state);
                throw new PipelineException($e->getMessage(), 0, $e);
            }
            if ($state->get(PipelineStateParam::INTERRUPT_PIPELINE) === true) {
                $this->log('Pipeline interrupted', ['task' => $taskName]);
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
            $this->log('Start cleaning up');
            $this->proceedTask($state, $this->cleanupTask);
        } else {
            $this->log('Skip cleaning up');
        }
    }

    /**
     * Записывает в лог данные.
     */
    private function log(string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log(
                LogLevel::INFO,
                $message,
                $this->createLoggerContext($context)
            );
        }
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
