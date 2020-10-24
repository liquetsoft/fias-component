<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Helper;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

/**
 * Класс, который содержит функции для работы с файловой системой.
 */
class FileSystemHelper
{
    /**
     * Удаляет указанный файл или папку.
     *
     * @param SplFileInfo $fileInfo
     */
    public static function remove(SplFileInfo $fileInfo): void
    {
        if ($fileInfo->isDir()) {
            $it = new RecursiveDirectoryIterator(
                $fileInfo->getRealPath(),
                RecursiveDirectoryIterator::SKIP_DOTS
            );
            $files = new RecursiveIteratorIterator(
                $it,
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } elseif ($file->isFile()) {
                    unlink($file->getRealPath());
                }
            }
            rmdir($fileInfo->getRealPath());
        } elseif ($fileInfo->isFile()) {
            unlink($fileInfo->getRealPath());
        }
    }

    /**
     * Переносит файлы или папки по указанному пути.
     *
     * @param SplFileInfo $source
     * @param SplFileInfo $destination
     */
    public static function move(SplFileInfo $source, SplFileInfo $destination): void
    {
        if (!$source->isFile() && !$source->isDir()) {
            $message = sprintf("Can't find source object '%s' to moving.", $source->getPathname());
            throw new RuntimeException($message);
        }

        $source = $source->getRealPath();
        $destination = $destination->getPathname();

        if (!rename($source, $destination)) {
            $message = sprintf("Error while moving '%s' to '%s'.", $source, $destination);
            throw new RuntimeException($message);
        }
    }
}
