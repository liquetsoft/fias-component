<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline;

/**
 * Интерфейс для объекта, который производит одну атомарную операцию,
 * необходимую для загрузки данных ФИАС из файлов в базу данных.
 */
interface PipelineTask
{
    /**
     * Запускает задачу на исполнение.
     *
     * @throws \Exception
     */
    public function run(PipelineState $state): PipelineState;
}
