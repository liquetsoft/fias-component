<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Marvin255\FileSystemHelper\FileSystemHelper;
use Psr\Log\LogLevel;

/**
 * Задача, которая подготавливает все необходимые каталоги и файлы для процесса
 * установки/обновления.
 */
final class PrepareFolderTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    private readonly \SplFileInfo $folder;

    public function __construct(
        string $folder,
        private readonly FileSystemHelper $fs,
    ) {
        $trimmedFolder = rtrim(trim($folder, " \t\n\r\0\x0B"), '/');
        $this->folder = new \SplFileInfo($trimmedFolder);
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): State
    {
        $this->log(LogLevel::INFO, "Emptying '{$this->folder->getPathname()}' folder");
        $this->fs->mkdirIfNotExist($this->folder);
        $this->fs->emptyDir($this->folder);

        $downloadToFile = new \SplFileInfo($this->folder->getRealPath() . '/archive');
        $extractToFolder = new \SplFileInfo($this->folder->getRealPath() . '/extracted');

        $this->log(LogLevel::INFO, "Creating '{$this->folder->getRealPath()}/extracted' folder");
        $this->fs->mkdir($extractToFolder);

        return $state->setParameter(StateParameter::PATH_TO_DOWNLOAD_FILE, $downloadToFile->getPathname())
            ->setParameter(StateParameter::PATH_TO_EXTRACT_FOLDER, $extractToFolder->getPathname());
    }
}
