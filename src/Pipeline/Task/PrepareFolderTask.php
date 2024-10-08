<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Marvin255\FileSystemHelper\FileSystemFactory;
use Marvin255\FileSystemHelper\FileSystemHelperInterface;
use Psr\Log\LogLevel;

/**
 * Задача, которая подготавливает все необходимые каталоги и файлы для процесса
 * установки/обновления.
 */
final class PrepareFolderTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    private readonly \SplFileInfo $folder;

    private readonly FileSystemHelperInterface $fs;

    public function __construct(string $folder)
    {
        $trimmedFolder = rtrim(trim($folder, " \t\n\r\0\x0B"), '/');
        $parent = realpath(\dirname($trimmedFolder));

        if ($parent === false || !is_dir($parent) || !is_writable($parent)) {
            throw new \InvalidArgumentException("'{$parent}' folder doesn't exist or isn't writable");
        }

        $this->folder = new \SplFileInfo($trimmedFolder);
        $this->fs = FileSystemFactory::create();
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
