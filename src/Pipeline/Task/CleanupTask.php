<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Helper\FileSystemHelper;
use SplFileInfo;

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
            $state->getParameter(Task::DOWNLOAD_TO_FILE_PARAM),
            $state->getParameter(Task::EXTRACT_TO_FOLDER_PARAM),
        ];

        $toRemove = array_diff($toRemove, [null]);

        foreach ($toRemove as $fileInfo) {
            if ($fileInfo instanceof SplFileInfo) {
                FileSystemHelper::remove($fileInfo);
            }
        }
    }
}
