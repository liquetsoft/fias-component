<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Parallel;

use Liquetsoft\Fias\Component\Exception\ParallelException;

/**
 * Интерфейс для объекта, который выполняет указанные задачи параллельно.
 */
interface Pool
{
    /**
     * Добавляет задачу в план для выполнения.
     *
     * @param Task $task
     */
    public function addTask(Task $task): void;

    /**
     * Запускает все зарегистрированные задачи на выполенение.
     *
     * @throws ParallelException
     */
    public function run(): void;
}
