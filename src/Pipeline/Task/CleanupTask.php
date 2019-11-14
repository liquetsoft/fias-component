<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Helper\FileSystemHelper;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Psr\Log\LogLevel;
use SplFileInfo;

/**
 * Задача, которая удаляет все временные файлы, полученные во время импорта.
 */
class CleanupTask implements Task, LoggableTask
{
    use LoggableTaskTrait;

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
                $this->log(LogLevel::INFO, "Cleaning up '{$fileInfo->getRealPath()}' folder.");
                FileSystemHelper::remove($fileInfo);
            }
        }
    }
}
