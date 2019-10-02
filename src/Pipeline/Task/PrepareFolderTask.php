<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Helper\FileSystemHelper;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Psr\Log\LogLevel;
use SplFileInfo;
use InvalidArgumentException;

/**
 * Задача, которая подготавливает все необходимые каталоги и файлы для процесса
 * установки/обновления.
 */
class PrepareFolderTask implements Task, LoggableTask
{
    use LoggableTaskTrait;

    /**
     * @var SplFileInfo
     */
    protected $folder;

    /**
     * @param string $folder
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $folder)
    {
        $trimmedFolder = rtrim(trim($folder, " \t\n\r\0\x0B"), '/');
        $parent = realpath(dirname($trimmedFolder));

        if (!$parent || !is_dir($parent) || !is_writable($parent)) {
            throw new InvalidArgumentException(
                "'{$parent}' folder doesn't exist or isn't writable."
            );
        }

        $this->folder = new SplFileInfo($trimmedFolder);
    }

    /**
     * @inheritdoc
     */
    public function run(State $state): void
    {
        if ($this->folder->isDir()) {
            $this->log(LogLevel::INFO, "Emptying '{$this->folder->getRealPath()}' folder.");
            FileSystemHelper::remove($this->folder);
        }

        if (!mkdir($this->folder->getPathname())) {
            throw new TaskException("Can't create '" . $this->folder->getPathname() . "' folder.");
        }

        $downloadToFile = new SplFileInfo($this->folder->getRealPath() . '/archive');
        $extractToFolder = new SplFileInfo($this->folder->getRealPath() . '/extracted');

        $this->log(LogLevel::INFO, "Creating '{$this->folder->getRealPath()}/extracted' folder.");
        if (!mkdir($extractToFolder->getPathname())) {
            throw new TaskException("Can't create '" . $extractToFolder->getPathname() . "' folder.");
        }

        $state->setAndLockParameter(Task::DOWNLOAD_TO_FILE_PARAM, $downloadToFile);
        $state->setAndLockParameter(Task::EXTRACT_TO_FOLDER_PARAM, $extractToFolder);
    }
}
