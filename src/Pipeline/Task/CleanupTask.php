<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Marvin255\FileSystemHelper\FileSystemFactory;
use Marvin255\FileSystemHelper\FileSystemHelperInterface;
use Psr\Log\LogLevel;

/**
 * Задача, которая удаляет все временные файлы, полученные во время импорта.
 */
final class CleanupTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    private readonly FileSystemHelperInterface $fs;

    public function __construct()
    {
        $this->fs = FileSystemFactory::create();
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): State
    {
        $toRemove = [
            $state->getParameterString(StateParameter::PATH_TO_DOWNLOAD_FILE),
            $state->getParameterString(StateParameter::PATH_TO_EXTRACT_FOLDER),
        ];

        foreach ($toRemove as $path) {
            if ($path !== '') {
                $this->fs->removeIfExists($path);
                $this->log(
                    LogLevel::INFO,
                    'Item is cleaned up',
                    [
                        'path' => $path,
                    ]
                );
            }
        }

        return $state;
    }
}
