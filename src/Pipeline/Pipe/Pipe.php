<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Pipe;

use Liquetsoft\Fias\Component\Exception\PipeException;
use Liquetsoft\Fias\Component\Pipeline\State\State;

/**
 * Интерфейс для объекта, который включает в себя несколько операций для
 * загрузки или обновления данных и который поочередно их запускает.
 *
 * Между каждой операцией передается объект состояния, так операции могут
 * взаимодействовать друг с другом, при этом не обретая зависимостей.
 */
interface Pipe
{
    /**
     * Запускает все операции на выполнение.
     *
     * @throws PipeException
     */
    public function run(State $state): State;
}
