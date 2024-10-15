<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FilesDispatcher;

use Liquetsoft\Fias\Component\FiasFile\FiasFile;

/**
 * Интерфейс для объекта, который разбивает список файлов по потокам для параллельного запуска.
 */
interface FilesDispatcher
{
    /**
     * Разбивает список файлов по потокам.
     *
     * @param FiasFile[] $files
     *
     * @return FiasFile[][]
     */
    public function dispatch(array $files, int $processesCount = 6): array;
}
