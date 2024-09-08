<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Filter\Filter;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Psr\Log\LogLevel;

class SelectFilesToProceedTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    private EntityManager $entityManager;

    private ?Filter $filter;

    public function __construct(EntityManager $entityManager, ?Filter $filter = null)
    {
        $this->entityManager = $entityManager;
        $this->filter = $filter;
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): void
    {
        $folderParameter = $state->getParameter(StateParameter::EXTRACT_TO_FOLDER);
        $extractToFolder = $this->checkDirectory($folderParameter);

        $this->log(
            LogLevel::INFO,
            "Searching for files to proceed in '{$extractToFolder->getRealPath()}' folder."
        );

        $files = $this->getFilesForProceedFromFolder($extractToFolder);
        $state->setAndLockParameter(StateParameter::FILES_TO_PROCEED, $files);

        $this->log(
            LogLevel::INFO,
            'Found ' . \count($files) . ' file(s) to proceed',
            [
                'files' => $files,
            ]
        );
    }

    /**
     * Проверяет, что параметр директории для поиска файлов задан верно.
     *
     * @throws TaskException
     */
    private function checkDirectory($parameterValue): \SplFileInfo
    {
        if (!($parameterValue instanceof \SplFileInfo)) {
            throw new TaskException(
                "State parameter '" . StateParameter::EXTRACT_TO_FOLDER . "' must be an '" . \SplFileInfo::class . "' instance for '" . self::class . "'."
            );
        }

        if (!$parameterValue->isDir()) {
            throw new TaskException(
                "Path '{$parameterValue->getRealPath()}' must be an existed directory."
            );
        }

        return $parameterValue;
    }

    /**
     * Возвращает список файлов для обработки из указанной директории.
     *
     * @return string[]
     */
    private function getFilesForProceedFromFolder(\SplFileInfo $filesFolder): array
    {
        $files = [];

        $directoryIterator = new \RecursiveDirectoryIterator(
            $filesFolder->getRealPath(),
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        /** @var iterable<\SplFileInfo> */
        $iterator = new \RecursiveIteratorIterator($directoryIterator);

        foreach ($iterator as $fileInfo) {
            if ($this->isFileAllowed($fileInfo)) {
                $files[] = (string) $fileInfo->getRealPath();
            }
        }

        sort($files, \SORT_STRING);

        return $files;
    }

    /**
     * Проверяет следует ли добавлять файл к списку.
     */
    private function isFileAllowed(\SplFileInfo $fileInfo): bool
    {
        return ($this->filter === null || $this->filter->test($fileInfo))
            && ($this->isFileAllowedToInsert($fileInfo) || $this->isFileAllowedToDelete($fileInfo))
        ;
    }

    /**
     * Проверяет нужно ли файл обрабатывать для создания и обновления в рамках данного процесса.
     */
    private function isFileAllowedToInsert(\SplFileInfo $fileInfo): bool
    {
        $descriptor = $this->entityManager->getDescriptorByInsertFile($fileInfo->getFilename());

        return $descriptor !== null && $this->entityManager->getClassByDescriptor($descriptor) !== null;
    }

    /**
     * Проверяет нужно ли файл обрабатывать для удаления в рамках данного процесса.
     */
    private function isFileAllowedToDelete(\SplFileInfo $fileInfo): bool
    {
        $descriptor = $this->entityManager->getDescriptorByDeleteFile($fileInfo->getFilename());

        return $descriptor !== null && $this->entityManager->getClassByDescriptor($descriptor) !== null;
    }
}
