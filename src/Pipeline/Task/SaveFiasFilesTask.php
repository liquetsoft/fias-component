<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Helper\FileSystemHelper;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Psr\Log\LogLevel;
use SplFileInfo;

/**
 * Задача, которая перемещает загруженные и распакованные файлы ФИАС
 * по указанному адресу.
 */
class SaveFiasFilesTask implements Task, LoggableTask
{
    use LoggableTaskTrait;

    /**
     * @var array<string, string>
     */
    protected $movePaths;

    /**
     * @param string|null $moveArchiveTo
     * @param string|null $moveExtractedTo
     */
    public function __construct(?string $moveArchiveTo, ?string $moveExtractedTo)
    {
        $this->movePaths = [];

        if ($moveArchiveTo !== null) {
            $this->movePaths[Task::DOWNLOAD_TO_FILE_PARAM] = $moveArchiveTo;
        }

        if ($moveExtractedTo !== null) {
            $this->movePaths[Task::EXTRACT_TO_FOLDER_PARAM] = $moveExtractedTo;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): void
    {
        foreach ($this->movePaths as $paramName => $movePath) {
            $fileInfo = $state->getParameter($paramName);

            if (!($fileInfo instanceof SplFileInfo)) {
                continue;
            }

            $message = sprintf("Moving '%s' to '%s'.", $fileInfo->getRealPath(), $movePath);
            $this->log(LogLevel::INFO, $message);

            FileSystemHelper::move($fileInfo, new SplFileInfo($movePath));
        }
    }
}
