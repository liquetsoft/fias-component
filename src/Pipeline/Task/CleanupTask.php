<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Marvin255\FileSystemHelper\FileSystemFactory;
use Marvin255\FileSystemHelper\FileSystemHelperInterface;
use Psr\Log\LogLevel;
use SplFileInfo;

/**
 * Задача, которая удаляет все временные файлы, полученные во время импорта.
 */
class CleanupTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    private FileSystemHelperInterface $fs;

    public function __construct()
    {
        $this->fs = FileSystemFactory::create();
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): void
    {
        $toRemove = [
            $state->getParameter(StateParameter::DOWNLOAD_TO_FILE),
            $state->getParameter(StateParameter::EXTRACT_TO_FOLDER),
        ];

        $toRemove = array_diff($toRemove, [null]);

        foreach ($toRemove as $fileInfo) {
            if ($fileInfo instanceof SplFileInfo) {
                $this->log(LogLevel::INFO, "Cleaning up '{$fileInfo->getPathname()}' folder.");
                $this->fs->removeIfExists($fileInfo);
            }
        }
    }
}
