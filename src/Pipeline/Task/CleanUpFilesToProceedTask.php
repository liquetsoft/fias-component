<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Marvin255\FileSystemHelper\FileSystemFactory;
use Marvin255\FileSystemHelper\FileSystemHelperInterface;
use Psr\Log\LogLevel;

/**
 * Задача, которая удаляет по одному только те файлы, которые были в обработке.
 */
final class CleanUpFilesToProceedTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    private readonly FileSystemHelperInterface $fs;

    public function __construct(?FileSystemHelperInterface $fs)
    {
        $this->fs = $fs ?? FileSystemFactory::create();
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): State
    {
        $files = $state->getParameter(StateParameter::FILES_TO_PROCEED);
        if (!\is_array($files)) {
            throw TaskException::create("'%s' param must be an array", StateParameter::FILES_TO_PROCEED->value);
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
