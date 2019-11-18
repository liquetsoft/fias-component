<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FilesDispatcher;

/**
 * Интерфейс для объекта, который разбивает список файлов по потокам для параллельного запуска.
 */
interface FilesDispatcher
{
    /**
     * Разбивает список файлов для вставки по потокам.
     *
     * @param string[] $filesToInsert
     * @param int      $processesCount
     *
     * @return array[]
     */
    public function dispatchInsert(array $filesToInsert, int $processesCount = 6): array;

    /**
     * Разбивает список файлов для удаления по потокам.
     *
     * @param string[] $filesToDelete
     * @param int      $processesCount
     *
     * @return array[]
     */
    public function dispatchDelete(array $filesToDelete, int $processesCount = 6): array;
}
