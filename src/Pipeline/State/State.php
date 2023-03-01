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
    public function setParameter(string $parameterName, $parameterValue): State;

    /**
     * Задает константу состояния по его имени и запрещает изменение.
     *
     * @throws \InvalidArgumentException
     */
    public function setAndLockParameter(string $parameterName, $parameterValue): State;

    /**
     * Возвращает параметр состояния по его имени.
     */
    public function getParameter(string $parameterName);

    /**
     * Команда, которая отмечает, что нужно мягко прервать цепочку операций.
     */
    public function complete(): State;

    /**
     * Метод, который указывает, что цепочка должна быть прервана после текущей
     * операции.
     */
    public function isCompleted(): bool;
}
