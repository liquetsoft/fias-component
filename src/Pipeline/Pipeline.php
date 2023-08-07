<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline;

use Liquetsoft\Fias\Component\Exception\PipelineException;

/**
 * Интерфейс для объекта, который включает в себя несколько операций для
 * загрузки или обновления данных и который поочередно их запускает.
 *
 * Между каждой операцией передается объект состояния, так операции могут
 * взаимодействовать друг с другом, при этом не обретая зависимостей.
 */
interface Pipeline
{
    /**
     * Запускает все операции на выполнение.
     *
     * @throws PipelineException
     */
    public function run(PipelineState $state): void;
}
