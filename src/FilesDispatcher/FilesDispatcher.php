<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FilesDispatcher;

/**
 * Интерфейс для объекта, который разбивает список файлов по потокам для параллельного запуска.
 */
interface FilesDispatcher
{
    /**
     * Разбивает список файлов по потокам.
     *
     * @param string[] $files
     * @param int      $processesCount
     *
     * @return string[][]
     */
    public function dispatch(array $files, int $processesCount = 6): array;
}
