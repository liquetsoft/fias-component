<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Marvin255\FileSystemHelper\FileSystemFactory;
use Marvin255\FileSystemHelper\FileSystemHelperInterface;
use Psr\Log\LogLevel;
use SplFileInfo;

/**
 * Задача, которая подготавливает все необходимые каталоги и файлы для процесса
 * установки/обновления.
 */
class PrepareFolderTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    protected SplFileInfo $folder;

    private FileSystemHelperInterface $fs;

    /**
     * @param string $folder
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $folder)
    {
        $trimmedFolder = rtrim(trim($folder, " \t\n\r\0\x0B"), '/');
        $parent = realpath(\dirname($trimmedFolder));

        if (!$parent || !is_dir($parent) || !is_writable($parent)) {
            throw new InvalidArgumentException(
                "'{$parent}' folder doesn't exist or isn't writable."
            );
        }

        $this->folder = new SplFileInfo($trimmedFolder);
        $this->fs = FileSystemFactory::create();
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): void
    {
        $this->log(LogLevel::INFO, "Emptying '{$this->folder->getPathname()}' folder.");
        $this->fs->mkdirIfNotExist($this->folder);
        $this->fs->emptyDir($this->folder);

        $downloadToFile = new SplFileInfo($this->folder->getRealPath() . '/archive');
        $extractToFolder = new SplFileInfo($this->folder->getRealPath() . '/extracted');

        $this->log(LogLevel::INFO, "Creating '{$this->folder->getRealPath()}/extracted' folder.");
        $this->fs->mkdir($extractToFolder);

        $state->setAndLockParameter(State::DOWNLOAD_TO_FILE_PARAM, $downloadToFile);
        $state->setAndLockParameter(State::EXTRACT_TO_FOLDER_PARAM, $extractToFolder);
    }
}
