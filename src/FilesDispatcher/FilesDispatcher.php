<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FilesDispatcher;

use Liquetsoft\Fias\Component\Unpacker\UnpackerFile;

/**
 * Интерфейс для объекта, который разбивает список файлов по потокам для параллельного запуска.
 */
interface FilesDispatcher
{
    /**
     * Разбивает список файлов по потокам.
     *
     * @param UnpackerFile[] $files
     *
     * @return UnpackerFile[][]
     */
    public function dispatch(array $files, int $processesCount = 6): array;
}
