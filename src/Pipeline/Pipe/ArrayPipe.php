<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Pipe;

use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Exception\PipeException;
use InvalidArgumentException;
use Throwable;

/**
 * Объект, который содержит внутренний массив со списком операций для
 * исполнения.
 */
class ArrayPipe implements Pipe
{
    /**
     * @var Task[]
     */
    protected $tasks;

    /**
     * @var Task|null
     */
    protected $cleanupTask;

    /**
     * @param iterable $tasks       Список задач, которые должны быть исполены данной очередью
     * @param Task     $cleanupTask Задача, которая будет выполнена после исключения или по успешному завершению очереди
     *
     * @throws InvalidArgumentException
     */
    public function __construct(iterable $tasks, ?Task $cleanupTask = null)
    {
        $this->tasks = [];
        foreach ($tasks as $key => $task) {
            if (!($task instanceof Task)) {
                throw new InvalidArgumentException(
                    "Task with key '{$key}' must be an '" . Task::class . "' instance."
                );
            }
            $this->tasks[] = $task;
        }

        $this->cleanupTask = $cleanupTask;
    }

    /**
     * @inheritdoc
     */
    public function run(State $state): Pipe
    {
        foreach ($this->tasks as $task) {
            try {
                $task->run($state);
            } catch (Throwable $e) {
                $this->cleanup($state);
                $message = "Error while running '" . get_class($task) . "' task.";
                throw new PipeException($message, 0, $e);
            }
            if ($state->isCompleted()) {
                break;
            }
        }

        $state->complete();
        $this->cleanup($state);

        return $this;
    }

    /**
     * Обработка завершения задачи.
     *
     * @param State $state
     */
    protected function cleanup(State $state): void
    {
        if ($this->cleanupTask) {
            $this->cleanupTask->run($state);
        }
    }
}
