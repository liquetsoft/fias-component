<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Unpacker\Unpacker;
use Psr\Log\LogLevel;

/**
 * Задача, которая распаковывает архив из файла в папку, указанные в состоянии.
 */
final class UnpackTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    public function __construct(private readonly Unpacker $unpacker)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): void
    {
        $source = $state->getParameterString(StateParameter::PATH_TO_DOWNLOAD_FILE);
        if ($source === '') {
            throw new TaskException('Source path must be a non empty string');
        }

        $destination = $state->getParameterString(StateParameter::PATH_TO_EXTRACT_FOLDER);
        if ($destination === '') {
            throw new TaskException('Destination path must be a non empty string');
        }

        $this->log(LogLevel::INFO, "Extracting '{$source}' to '{$destination}'");

        $this->unpacker->unpack(
            new \SplFileInfo($source),
            new \SplFileInfo($destination)
        );
    }
}
