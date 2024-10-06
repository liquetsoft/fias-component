<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Filter\Filter;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Psr\Log\LogLevel;

/**
 * Задание, которое проверяет все файлы в распакованном архиве ФИАС
 * и выбирает только те, которые можно обработать.
 */
final class SelectFilesToProceedTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly ?Filter $filter = null,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): State
    {
        $extractToFolderPath = $state->getParameterString(StateParameter::PATH_TO_EXTRACT_FOLDER);
        $extractToFolder = $this->checkDirectory($extractToFolderPath);

        $this->log(
            LogLevel::INFO,
            "Searching for files to proceed in '{$extractToFolder->getRealPath()}' folder"
        );

        $files = $this->getFilesForProceedFromFolder($extractToFolder);

        $this->log(
            LogLevel::INFO,
            'Found ' . \count($files) . ' file(s) to proceed',
            [
                'files' => $files,
            ]
        );

        return $state->setParameter(StateParameter::FILES_TO_PROCEED, $files);
    }

    /**
     * Проверяет, что параметр директории для поиска файлов задан верно.
     *
     * @throws TaskException
     */
    private function checkDirectory(string $path): \SplFileInfo
    {
        $dir = new \SplFileInfo($path);

        if (!$dir->isDir()) {
            throw new TaskException(
                "Path '{$dir->getPathname()}' must be an existed directory"
            );
        }

        return $dir;
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
