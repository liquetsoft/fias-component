<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\State;
use SplFileInfo;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Задача, которая удаляет все временные файлы, полученные во время импорта.
 */
class CleanupTask implements Task
{
    /**
     * @inheritdoc
     */
    public function run(State $state): void
    {
        $toRemove = [
            $state->getParameter('downloadTo'),
            $state->getParameter('extractTo'),
        ];

        $toRemove = array_diff($toRemove, [null]);

        foreach ($toRemove as $fileinfo) {
            if ($fileinfo instanceof SplFileInfo) {
                $this->remove($fileinfo);
            }
        }
    }

    /**
     * Удаляет указанный файл или папку.
     *
     * @param SplFileInfo $fileInfo
     */
    protected function remove(SplFileInfo $fileInfo): void
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
