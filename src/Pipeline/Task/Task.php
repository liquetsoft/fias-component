<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\State;

/**
 * Интерфейс для объекта, который производит одну атомарную операцию,
 * необходимую для загрузки данных ФИАС из файлов в базу данных.
 */
interface Task
{
    /**
     * Запускает задачу на исполнение.
     *
     * @param State $state
     *
     * @throws \Exception
     */
    public function run(State $state): void;
}
