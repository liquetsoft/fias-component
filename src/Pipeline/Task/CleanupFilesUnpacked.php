<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Marvin255\FileSystemHelper\FileSystemHelper;
use Psr\Log\LogLevel;

/**
 * Задача, которая удаляет по одному только те файлы, которые были распакованы.
 */
final class CleanupFilesUnpacked implements LoggableTask, Task
{
    use LoggableTaskTrait;

    public function __construct(private readonly FileSystemHelper $fs)
    {
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function run(State $state): State
    {
        $files = $state->getParameter(StateParameter::FILES_UNPACKED);
        if (!\is_array($files)) {
            return $state;
        }

        foreach ($files as $file) {
            $this->fs->removeIfExists((string) $file);
            $this->log(
                LogLevel::INFO,
                'Item is cleaned up',
                [
                    'path' => $file,
                ]
            );
        }

        return $state;
    }
}
