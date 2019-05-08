<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Helper;

use SplFileInfo;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Хэлпер, который содержит функции для работы с файловой системой.
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
}
