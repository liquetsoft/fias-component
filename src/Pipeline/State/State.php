<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\State;

/**
 * Интерфейс для объекта, который передает состояние между операциями.
 */
interface State
{
    /**
     * Задает параметр состояния по его имени.
     *
     * @throws \InvalidArgumentException
     */
    public function setParameter(StateParameter $parameter, mixed $parameterValue): self;

    /**
     * Команда, которая отмечает, что нужно мягко прервать цепочку операций.
     */
    public function complete(): self;

    /**
     * Возвращает параметр состояния по его имени.
     */
    public function getParameter(StateParameter $parameter, mixed $default = null): mixed;

    /**
     * Возвращает параметр состояния по его имени и приводит к целому типу.
     */
    public function getParameterInt(StateParameter $parameter, int $default = 0): int;

    /**
     * Возвращает параметр состояния по его имени и приводит к строковому типу.
     */
    public function getParameterString(StateParameter $parameter, string $default = ''): string;

    /**
     * Метод, который указывает, что цепочка должна быть прервана после текущей
     * операции.
     */
    public function isCompleted(): bool;
}
