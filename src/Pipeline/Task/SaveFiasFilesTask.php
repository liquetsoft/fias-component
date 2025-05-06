<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Marvin255\FileSystemHelper\FileSystemHelper;
use Psr\Log\LogLevel;

/**
 * Задача, которая перемещает загруженные и распакованные файлы ФИАС
 * по указанному адресу.
 */
final class SaveFiasFilesTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    public function __construct(
        private readonly ?string $moveArchiveTo,
        private readonly ?string $moveExtractedTo,
        private readonly FileSystemHelper $fs,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function run(State $state): State
    {
        $movePaths = [];
        if ($this->moveArchiveTo !== null) {
            $movePaths[StateParameter::PATH_TO_DOWNLOAD_FILE->value] = $this->moveArchiveTo;
        }
        if ($this->moveExtractedTo !== null) {
            $movePaths[StateParameter::PATH_TO_EXTRACT_FOLDER->value] = $this->moveExtractedTo;
        }

        foreach ($movePaths as $paramName => $moveTo) {
            $moveFrom = $state->getParameterString(StateParameter::from($paramName));
            $this->log(LogLevel::INFO, "Moving '{$moveFrom}' to '{$moveTo}'");
            $this->fs->rename($moveFrom, $moveTo);
        }

        return $state;
    }
}
