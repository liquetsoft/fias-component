<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasFileSelector;

use Liquetsoft\Fias\Component\Exception\FiasFileSelectorException;

/**
 * Интерфейс для объекта, который хранит описание файла для внутренней обработки.
 */
interface FiasFileSelectorFile
{
    /**
     * Возвращает путь до файла.
     */
    public function getPath(): string;

    /**
     * Возвращает размер файла.
     */
    public function getSize(): int;

    /**
     * Возвращает путь до архива, если файл находится в архиве.
     *
     * @throws FiasFileSelectorException
     */
    public function getPathToArchive(): string;

    /**
     * Возвращает правду, если файл находится в архиве.
     */
    public function isArchived(): bool;
}
