<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Pipe;

use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Exception\PipeException;

/**
 * Интерфейс для объекта, который включает в себя несколько операций для
 * загрузки или обновления данных и котороый поочередно их запускает.
 *
 * Между каждой операцией переается объект состояния, так операции могут
 * взаимодействовать друг с другом, при этом не обретая зависимостей.
 */
interface Pipe
{
    /**
     * Запускает все операции на выполнение.
     *
     * @param State $state
     *
     * @return Pipe
     *
     * @throws PipeException
     */
    public function run(State $state): Pipe;
}
