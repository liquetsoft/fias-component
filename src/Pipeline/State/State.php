<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\State;

use InvalidArgumentException;

/**
 * Интерфейс для объекта, который передает состояние между операциями.
 */
interface State
{
    /**
     * Задает параметр состояния по его имени.
     *
     * @param string $parameterName
     * @param mixed  $parameterValue
     *
     * @return State
     *
     * @throws InvalidArgumentException
     */
    public function setParameter(string $parameterName, $parameterValue): State;

    /**
     * Задает константу состояния по его имени и запрещает изменение.
     *
     * @param string $parameterName
     * @param mixed  $parameterValue
     *
     * @return State
     *
     * @throws InvalidArgumentException
     */
    public function setAndLockParameter(string $parameterName, $parameterValue): State;

    /**
     * Возвращает параметр состояния по его имени.
     *
     * @param string $parameterName
     *
     * @return mixed
     */
    public function getParameter(string $parameterName);

    /**
     * Команда, которая отмечает, что нужно мягко прервать цепочку операций.
     *
     * @return State
     */
    public function complete(): State;

    /**
     * Метод, который указывает, что цепочка должна быть прервана после текущей
     * операции.
     *
     * @return bool
     */
    public function isCompleted(): bool;
}
