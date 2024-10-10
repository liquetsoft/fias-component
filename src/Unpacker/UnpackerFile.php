<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

/**
 * Интерфейс для объекта, который представляет файл внутри архива.
 */
interface UnpackerFile extends \Stringable
{
    /**
     * Возвращает путь к архиву, в котором хранится объект.
     */
    public function getArchiveFile(): \SplFileInfo;

    /**
     * Возвращает индекс заархивированного объекта.
     */
    public function getIndex(): int;

    /**
     * Возвращает размер заархивированного объекта.
     */
    public function getSize(): int;

    /**
     * Возвращает имя заархивированного объекта.
     */
    public function getName(): string;
}
