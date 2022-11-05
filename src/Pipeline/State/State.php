<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\State;

use InvalidArgumentException;

/**
 * Интерфейс для объекта, который передает состояние между операциями.
 */
interface State
{
    public const FILES_TO_PROCEED = 'files_to_proceed';

    public const FIAS_VERSION_PARAM = 'fias_version';

    public const EXTRACT_TO_FOLDER_PARAM = 'extract_to';

    public const FIAS_INFO_PARAM = 'fias_info';

    public const DOWNLOAD_TO_FILE_PARAM = 'download_to';

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
