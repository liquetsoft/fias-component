<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Unpacker\Unpacker;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Psr\Log\LogLevel;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use SplFileInfo;

/**
 * Задача, которая распаковывает архив из файла в папку, указанные в состоянии.
 */
class UnpackTask implements Task, LoggableTask
{
    use LoggableTaskTrait;

    /**
     * @var Unpacker
     */
    protected $unpacker;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param Unpacker $unpacker
     */
    public function __construct(Unpacker $unpacker, EntityManager $entityManager)
    {
        $this->unpacker = $unpacker;
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritdoc
     */
    public function run(State $state): void
    {
        $source = $state->getParameter(Task::DOWNLOAD_TO_FILE_PARAM);
        if (!($source instanceof SplFileInfo)) {
            throw new TaskException(
                "State parameter '" . Task::DOWNLOAD_TO_FILE_PARAM . "' must be an '" . SplFileInfo::class . "' instance for '" . self::class . "'."
            );
        }

        $destination = $state->getParameter(Task::EXTRACT_TO_FOLDER_PARAM);
        if (!($destination instanceof SplFileInfo)) {
            throw new TaskException(
                "State parameter '" . Task::EXTRACT_TO_FOLDER_PARAM . "' must be an '" . SplFileInfo::class . "' instance for '" . self::class . "'."
            );
        }

        $this->log(
            LogLevel::INFO,
            "Extracting '{$source->getRealPath()}' to '{$destination->getPathname()}'."
        );

        $classes = $this->entityManager->getBindedClasses();
        $files_to_extract = [];
        foreach ($classes as $class) {
            $descriptor = $this->entityManager->getDescriptorByClass($class);
            if ($descriptor !== null) {
                $files_to_extract[] = $descriptor->getInsertFileMask();
                $files_to_extract[] = $descriptor->getDeleteFileMask();
            }
        }
        
        $this->unpacker->unpack($source, $destination, $files_to_extract);

        $size = $this->getDirSize($destination->getRealPath());
        $state->setAndLockParameter(Task::FIAS_SIZE, $size);
    }

    /**
     * Возвращает объем файлов в папке
     *
     * @param string $directory
     *
     * @return int
     */
    protected function getDirSize(string $directory): int
    {
        $size = 0;
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
        ) as $file) {
            $size+=$file->getSize();
        }
        return $size;
    }
}
