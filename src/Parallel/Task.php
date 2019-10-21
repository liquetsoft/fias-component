<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Parallel;

use Closure;

/**
 * Интерфейс для объекта с задачей для параллельного выполнения.
 */
interface Task
{
    /**
     * Возвращает объект с функцией для текущей задачи.
     *
     * @return Closure
     */
    public function getClosure(): Closure;

    /**
     * Возвращает набор параметров для текущей задачи.
     *
     * @return array
     */
    public function getParams(): array;

    /**
     * Возвращает номер треда (от 0 до максимального количества тредов), в котором следует выполнить задачу.
     *
     * @return int|null
     */
    public function getThreadNumber(): ?int;
}
