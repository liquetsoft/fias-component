<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasFileSelector;

use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\FiasFile\FiasFileFactory;
use Liquetsoft\Fias\Component\Filter\Filter;
use Marvin255\FileSystemHelper\FileSystemHelper;

/**
 * Базовая реализация объекта, который выбирает файлы из указанной папки
 * для последующей обработки.
 */
final class FiasFileSelectorDir implements FiasFileSelector
{
    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly FileSystemHelper $fs,
        private readonly ?Filter $filter = null,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function selectFiles(\SplFileInfo $source): array
    {
        if (!$source->isDir()) {
            return [];
        }

        $selectedFiles = [];
        $iterator = $this->fs->createDirectoryIterator($source);
        foreach ($iterator as $object) {
            if ($object->isFile() && $this->isFileAllowedForSelect($object)) {
                $selectedFiles[] = FiasFileFactory::createFromSplFileInfo($object);
            }
        }

        return $selectedFiles;
    }

    /**
     * Проверяет, что файл подходит для обработки.
     */
    private function isFileAllowedForSelect(\SplFileInfo $file): bool
    {
        return $file->getSize() > 0
            && $this->filter?->test($file) !== false
            && (
                $this->isFileAllowedToInsert($file->getPathname())
                || $this->isFileAllowedToDelete($file->getPathname())
            );
    }

    /**
     * Проверяет нужно ли файл обрабатывать для создания и обновления в рамках данного процесса.
     */
    private function isFileAllowedToInsert(string $file): bool
    {
        $descriptor = $this->entityManager->getDescriptorByInsertFile($file);

        return $descriptor !== null && $this->entityManager->getClassByDescriptor($descriptor) !== null;
    }

    /**
     * Проверяет нужно ли файл обрабатывать для удаления в рамках данного процесса.
     */
    private function isFileAllowedToDelete(string $file): bool
    {
        $descriptor = $this->entityManager->getDescriptorByDeleteFile($file);

        return $descriptor !== null && $this->entityManager->getClassByDescriptor($descriptor) !== null;
    }
}
