<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Unpacker\Unpacker;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFile;
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
    public function run(State $state): State
    {
        $rawFiles = $state->getParameter(StateParameter::FILES_TO_PROCEED);
        if (!\is_array($rawFiles)) {
            throw TaskException::create("'%s' param must be an array", StateParameter::FILES_TO_PROCEED->value);
        }

        $files = [];
        foreach ($rawFiles as $rawFile) {
            if ($rawFile instanceof UnpackerFile) {
                $files[] = $rawFile;
            }
        }

        $destination = $state->getParameterString(StateParameter::PATH_TO_EXTRACT_FOLDER);
        if ($destination === '') {
            throw new TaskException('Destination path must be a non empty string');
        }

        $unpackedFiles = [];
        foreach ($files as $file) {
            $res = $this->unpacker->unpackFile(
                $file->getArchiveFile(),
                $file->getName(),
                new \SplFileInfo($destination)
            );
            $unpackedFiles[] = $res->getRealPath();
            $this->log(
                LogLevel::INFO,
                'File is unpacked',
                [
                    'name' => $file->getName(),
                    'archive' => $file->getArchiveFile()->getPathname(),
                    'destination' => $destination,
                    'path' => $res->getRealPath(),
                ]
            );
        }

        return $state->setParameter(StateParameter::FILES_TO_PROCEED, $unpackedFiles);
    }
}
