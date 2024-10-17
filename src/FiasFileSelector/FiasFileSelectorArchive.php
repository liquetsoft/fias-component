<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasFileSelector;

use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Filter\Filter;
use Liquetsoft\Fias\Component\Unpacker\Unpacker;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFile;

/**
 * Базовая реализация объекта, который выбирает файлы из архива
 * для последующей обработки.
 */
final class FiasFileSelectorArchive implements FiasFileSelector
{
    public function __construct(
        private readonly Unpacker $unpacker,
        private readonly EntityManager $entityManager,
        private readonly ?Filter $filter = null,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function supportSource(\SplFileInfo $source): bool
    {
        return $this->unpacker->isArchive($source);
    }

    /**
     * {@inheritdoc}
     */
    public function selectFiles(\SplFileInfo $source): array
    {
        $selectedFiles = [];
        foreach ($this->unpacker->getListOfFiles($source) as $file) {
            if ($this->isFileAllowedForSelect($file)) {
                $selectedFiles[] = $file;
            }
        }

        return $selectedFiles;
    }

    /**
     * Проверяет, что файл подходит для обработки.
     */
    private function isFileAllowedForSelect(UnpackerFile $file): bool
    {
        $fileName = pathinfo($file->getName(), \PATHINFO_BASENAME);

        return $file->getSize() > 0
            && $this->filter?->test($file) !== false
            && (
                $this->isFileAllowedToInsert($fileName)
                || $this->isFileAllowedToDelete($fileName)
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
