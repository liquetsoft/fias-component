<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasFile\FiasFile;
use Liquetsoft\Fias\Component\Pipeline\Pipe\Pipe;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFile;

/**
 * Задача, которая применяет вложенную цепочку задач для каждого файла из состояния.
 */
final class ApplyNestedPipelineToFileTask implements Task
{
    public function __construct(private readonly Pipe $pipe)
    {
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
            $fileState = $state->setParameter(
                StateParameter::FILES_TO_PROCEED,
                $this->createNewFilesArrayFromFile($file)
            );
            $this->pipe->run($fileState);
        }

        return $state;
    }

    /**
     * Превращает файл в новый массив для StateParameter::FILES_TO_PROCEED.
     */
    private function createNewFilesArrayFromFile(mixed $file): array
    {
        if (($file instanceof FiasFile) && !($file instanceof UnpackerFile)) {
            return [
                $file->getName(),
            ];
        }

        return [
            $file,
        ];
    }
}
