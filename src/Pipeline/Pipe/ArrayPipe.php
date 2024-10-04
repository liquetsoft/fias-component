<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Pipe;

use Liquetsoft\Fias\Component\Exception\PipeException;
use Liquetsoft\Fias\Component\Helper\IdHelper;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\LoggableTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Объект, который содержит внутренний массив со списком операций для исполнения.
 */
final class ArrayPipe implements Pipe
{
    private const LOG_PARAM_NAME_TASK = 'task';
    private const LOG_PARAM_NAME_EXCEPTION = 'exception';
    private const LOG_PARAM_NAME_PIPELINE_ID = 'pipeline_id';
    private const LOG_PARAM_NAME_PIPELINE_CLASS = 'pipeline_class';

    /**
     * @param iterable<Task> $tasks
     */
    public function __construct(
        private readonly iterable $tasks,
        private readonly ?Task $cleanupTask = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function run(State $state): Pipe
    {
        $this->checkAndSetPipelineId($state);
        $this->proceedStart($state);

        foreach ($this->tasks as $task) {
            try {
                $this->proceedTask($state, $task);
            } catch (\Throwable $e) {
                $this->proceedException($state, $task, $e);
            }
            if ($state->isCompleted()) {
                break;
            }
        }

        $this->proceedComplete($state);
        $this->proceedCleanup($state);

        return $this;
    }

    /**
     * Добавить pipeline id в состояние, если id еще не указан.
     */
    private function checkAndSetPipelineId(State $state): void
    {
        if ($state->getParameterString(StateParameter::PIPELINE_ID) === '') {
            $state->setAndLockParameter(
                StateParameter::PIPELINE_ID,
                IdHelper::createUniqueId()
            );
        }
    }

    /**
     * Обработка запуска очереди.
     */
    private function proceedStart(State $state): void
    {
        $this->log($state, 'Pipeline started');
    }

    /**
     * Запускает задачу на исполнение.
     */
    private function proceedTask(State $state, Task $task): void
    {
        $taskName = $this->getTaskId($task);

        $this->log($state, 'Task started', [
            self::LOG_PARAM_NAME_TASK => $taskName,
        ]);

        $this->injectLoggerToTask($state, $task);
        $task->run($state);

        $this->log($state, 'Task completed', [
            self::LOG_PARAM_NAME_TASK => $taskName,
        ]);
    }

    /**
     * Обрабатывает исключение во время работы очереди.
     */
    private function proceedException(State $state, Task $task, \Throwable $e): void
    {
        $this->logException($state, $e, [
            self::LOG_PARAM_NAME_TASK => $this->getTaskId($task),
        ]);

        $this->proceedCleanup($state);

        throw new PipeException(
            message: $e->getMessage(),
            previous: $e
        );
    }

    /**
     * Обработка завершения задачи.
     */
    private function proceedCleanup(State $state): void
    {
        if ($this->cleanupTask) {
            $this->log($state, 'Clean up started');
            $this->proceedTask($state, $this->cleanupTask);
        } else {
            $this->log($state, 'Clean up skipped');
        }
    }

    /**
     * Обработка завершения очереди.
     */
    private function proceedComplete(State $state): void
    {
        $state->complete();
        $this->log($state, 'Pipeline completed');
    }

    /**
     * Записывает в лог данные.
     */
    private function log(State $state, string $message, array $context = []): void
    {
        if ($this->logger === null) {
            return;
        }

        $context = $this->createLoggerContext($state, $context);
        $this->logger->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Записывает в лог исключение.
     */
    private function logException(State $state, \Throwable $e, array $context = []): void
    {
        if ($this->logger === null) {
            return;
        }

        $context[self::LOG_PARAM_NAME_EXCEPTION] = $e;
        $context = $this->createLoggerContext($state, $context);

        $this->logger->log(LogLevel::INFO, $e->getMessage(), $context);
    }

    /**
     * Добавляет объект для записи логов в операцию, если операция это поддерживает.
     */
    private function injectLoggerToTask(State $state, Task $task): void
    {
        if ($task instanceof LoggableTask && $this->logger) {
            $context = $this->createLoggerContext(
                $state,
                [
                    self::LOG_PARAM_NAME_TASK => $this->getTaskId($task),
                ]
            );
            $task->injectLogger($this->logger, $context);
        }
    }

    /**
     * Возвращает контекст для записи логов по умолчанию.
     */
    private function createLoggerContext(State $state, array $currentContext = []): array
    {
        $defaultContext = [
            self::LOG_PARAM_NAME_PIPELINE_ID => $state->getParameterString(StateParameter::PIPELINE_ID),
            self::LOG_PARAM_NAME_PIPELINE_CLASS => \get_class($this),
        ];

        return array_merge($defaultContext, $currentContext);
    }

    /**
     * Возвращает идентификатор операции.
     */
    private function getTaskId(Task $task): string
    {
        return \get_class($task);
    }
}
