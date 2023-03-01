<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Marvin255\FileSystemHelper\FileSystemFactory;
use Marvin255\FileSystemHelper\FileSystemHelperInterface;
use Psr\Log\LogLevel;

/**
 * Задача, которая перемещает загруженные и распакованные файлы ФИАС
 * по указанному адресу.
 */
class SaveFiasFilesTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    /**
     * @var array<string, string>
     */
    protected array $movePaths;

    private FileSystemHelperInterface $fs;

    public function __construct(?string $moveArchiveTo, ?string $moveExtractedTo)
    {
        $this->movePaths = [];

        if ($moveArchiveTo !== null) {
            $this->movePaths[StateParameter::DOWNLOAD_TO_FILE] = $moveArchiveTo;
        }

        if ($moveExtractedTo !== null) {
            $this->movePaths[StateParameter::EXTRACT_TO_FOLDER] = $moveExtractedTo;
        }

        $this->fs = FileSystemFactory::create();
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): void
    {
        foreach ($this->movePaths as $paramName => $movePath) {
            $fileInfo = $state->getParameter($paramName);
            if ($fileInfo instanceof \SplFileInfo) {
                $message = sprintf("Moving '%s' to '%s'.", $fileInfo->getRealPath(), $movePath);
                $this->log(LogLevel::INFO, $message);
                $this->fs->rename($fileInfo, $movePath);
            }
        }
    }
}
