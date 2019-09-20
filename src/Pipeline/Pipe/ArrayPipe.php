<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Pipe;

use Exception;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Exception\PipeException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Ramsey\Uuid\Uuid;
use Throwable;

/**
 * Объект, который содержит внутренний массив со списком операций для исполнения.
 */
class ArrayPipe implements Pipe
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var Task[]
     */
    protected $tasks;

    /**
     * @var Task|null
     */
    protected $cleanupTask;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * @param iterable             $tasks       Список задач, которые должны быть исполены данной очередью
     * @param Task|null            $cleanupTask Задача, которая будет выполнена после исключения или по успешному завершению очереди
     * @param LoggerInterface|null $logger      PSR-3 совместимый логгер
     *
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function __construct(iterable $tasks, ?Task $cleanupTask = null, ?LoggerInterface $logger = null)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->tasks = $this->checkAndReturnTaskArray($tasks);
        $this->cleanupTask = $cleanupTask;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function run(State $state): Pipe
    {
        $this->proceedStart($state);

        foreach ($this->tasks as $task) {
            try {
                $this->proceedTask($state, $task);
            } catch (Throwable $e) {
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
     * Обработка запуска очереди.
     *
     * @param State $state
     */
    protected function proceedStart(State $state): void
    {
        $this->log(LogLevel::INFO, "Start  '" . get_class($this) . "' pipeline.");
    }

    /**
     * Запускает задачу на исполнение.
     *
     * @param State $state
     * @param Task  $task
     *
     * @throws Exception
     */
    protected function proceedTask(State $state, Task $task): void
    {
        $taskName = get_class($task);
        $this->log(
            LogLevel::INFO,
            "Start '{$taskName}' task.",
            ['task' => $taskName]
        );
        $task->run($state);
        $this->log(
            LogLevel::INFO,
            "Complete '{$taskName}' task.",
            ['task' => $taskName]
        );
    }

    /**
     * Обрабатывает исключение во время работы очереди.
     *
     * @param Task      $task
     * @param State     $state
     * @param Throwable $e
     *
     * @throws Exception
     */
    protected function proceedException(State $state, Task $task, Throwable $e): void
    {
        $taskName = get_class($task);
        $message = "Error while running {$taskName} task: {$e->getMessage()}";

        $this->log(LogLevel::ERROR, $message, [
            'exception' => $e,
            'task' => $taskName,
        ]);

        $this->proceedCleanup($state);

        throw new PipeException($message, 0, $e);
    }

    /**
     * Обработка завершения задачи.
     *
     * @param State $state
     *
     * @throws Exception
     */
    protected function proceedCleanup(State $state): void
    {
        if ($this->cleanupTask) {
            $this->log(LogLevel::INFO, 'Start cleaning up.');
            $this->proceedTask($state, $this->cleanupTask);
        } else {
            $this->log(LogLevel::INFO, 'Skip cleaning up.');
        }
    }

    /**
     * Обработка завершения очереди.
     *
     * @param State $state
     */
    protected function proceedComplete(State $state): void
    {
        $state->complete();
        $this->log(LogLevel::INFO, "Complete '" . get_class($this) . "' pipeline.");
    }

    /**
     * Записывает в лог данные.
     *
     * @param string $level
     * @param string $message
     * @param array  $context
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $context['pipeline'] = get_class($this);
            $context['pipeline_id'] = $this->id;
            $this->logger->log($level, "Pipeline {$this->id}. {$message}", $context);
        }
    }

    /**
     * Проверяет все объекты массива, чтобы они были валидными задачами и возвращает его.
     *
     * @param iterable $tasks
     *
     * @return Task[]
     *
     * @throws InvalidArgumentException
     */
    protected function checkAndReturnTaskArray(iterable $tasks): array
    {
        $return = [];

        foreach ($tasks as $key => $task) {
            if (!($task instanceof Task)) {
                throw new InvalidArgumentException(
                    "Task with key '{$key}' must be an '" . Task::class . "' instance."
                );
            }
            $return[] = $task;
        }

        return $return;
    }
}
