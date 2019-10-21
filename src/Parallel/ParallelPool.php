<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Parallel;

use Liquetsoft\Fias\Component\Exception\ParallelException;
use parallel\Runtime;
use InvalidArgumentException;
use Throwable;

/**
 * Пул запросов для выполнения задач параллельно.
 *
 * Позволяет зарегистрировать и запустить несколько задач параллельно с помощью php parallel.
 *
 * @see https://www.php.net/manual/ru/book.parallel.php
 */
class ParallelPool implements Pool
{
    /**
     * Путь к файлу с автозагрузчиком для задач.
     *
     * @var string|null
     */
    protected $pathToAutoload;

    /**
     * Максимальное количество задач, которые могут выполняться параллельно.
     *
     * @var int
     */
    protected $maxParallelThreads;

    /**
     * Список задач для выполнения.
     *
     * @var Task[]
     */
    protected $tasks = [];

    /**
     * @param string|null $pathToAutoload
     * @param int         $maxParallelThreads
     *
     * @throws InvalidArgumentException
     */
    public function __construct(?string $pathToAutoload = null, int $maxParallelThreads = 4)
    {
        if ($pathToAutoload !== null && !file_exists($pathToAutoload)) {
            throw new InvalidArgumentException(
                "Path '{$pathToAutoload}' for autoloader not found."
            );
        }

        if ($maxParallelThreads < 1) {
            throw new InvalidArgumentException(
                'Max parallel threads parameter must be more than 0.'
            );
        }

        $this->pathToAutoload = $pathToAutoload;
        $this->maxParallelThreads = $maxParallelThreads;
    }

    /**
     * @inheritDoc
     */
    public function addTask(Task $task): void
    {
        $this->tasks[] = $task;
    }

    /**
     * @inheritDoc
     */
    public function run(): void
    {
        try {
            $this->initializeAndRunRuntime();
            $this->clearTasks();
        } catch (Throwable $e) {
            throw new ParallelException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Создает список тредов для обработки задач и запускает задачи на обработку.
     */
    protected function initializeAndRunRuntime(): void
    {
        $tasksByThreads = $this->getTasksByThreads();

        $runtime = [];
        $future = [];

        try {
            //создаем треды и запускаем все задачи
            foreach ($tasksByThreads as $threadNumber => $threadTasks) {
                $runtime[] = $runtimeItem = $this->createRuntimeObject();
                foreach ($threadTasks as $task) {
                    $future[] = $runtimeItem->run($task->getClosure(), $task->getParams());
                }
            }
            //для того, чтобы получить исключения, обрабатываем ответы от задач
            foreach ($future as $futureItem) {
                $futureItem->value();
            }
        } finally {
            //закрываем все треды
            foreach ($runtime as $runtimeItem) {
                $runtimeItem->close();
            }
        }
    }

    /**
     * Возвращает список задач, распределенных по тредам.
     *
     * @return Task[][]
     *
     * @psalm-suppress PossiblyNullArrayOffset
     */
    protected function getTasksByThreads(): array
    {
        $tasks = $this->tasks;
        $threads = array_pad([], $this->maxParallelThreads, []);

        //сначала распределяем задачи, у которых явно задан тред
        foreach ($tasks as $taskNumber => $task) {
            $threadNumberFromTask = $task->getThreadNumber();
            if ($threadNumberFromTask !== null && $threadNumberFromTask >= 0 && $threadNumberFromTask < $this->maxParallelThreads) {
                $threads[$threadNumberFromTask][] = $task;
                unset($tasks[$taskNumber]);
            }
        }

        //оставшиеся задачи распределяем равномерно по всем тредам
        foreach ($tasks as $task) {
            $smallestThreadNumber = $this->getSmallestThreadNumber($threads);
            $threads[$smallestThreadNumber][] = $task;
        }

        return array_filter($threads);
    }

    /**
     * Создает объект треда.
     *
     * @return Runtime
     */
    protected function createRuntimeObject(): Runtime
    {
        if ($this->pathToAutoload) {
            $runtime = new Runtime($this->pathToAutoload);
        } else {
            $runtime = new Runtime();
        }

        return $runtime;
    }

    /**
     * Очищает список текущих задач пула.
     */
    protected function clearTasks(): void
    {
        $this->tasks = [];
    }

    /**
     * Возвращает номер треда, в котором меньше всего задач.
     *
     * @param array $threads
     *
     * @return int
     */
    protected function getSmallestThreadNumber(array $threads): int
    {
        $smallestThread = null;
        $smallestThreadNumber = null;

        foreach ($threads as $threadNumber => $thread) {
            $threadCount = count($thread);
            if ($smallestThread === null || $smallestThread > $threadCount) {
                $smallestThread = $threadCount;
                $smallestThreadNumber = $threadNumber;
            }
        }

        return (int) $smallestThreadNumber;
    }
}
