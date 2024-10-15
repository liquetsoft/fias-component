<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

use Liquetsoft\Fias\Component\FiasFile\FiasFile;

/**
 * Интерфейс для объекта, который представляет файл внутри архива.
 */
interface UnpackerFile extends FiasFile
{
    /**
     * Возвращает путь к архиву, в котором хранится объект.
     */
    public function getArchiveFile(): \SplFileInfo;

    /**
     * Возвращает индекс заархивированного объекта.
     */
    public function getIndex(): int;
}
