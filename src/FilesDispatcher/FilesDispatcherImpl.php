<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FilesDispatcher;

use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFile;

/**
 * Объект, который разбивает файлы на потоки по именам сущностей, к которым файлы относятся.
 */
final class FilesDispatcherImpl implements FilesDispatcher
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(array $files, int $processesCount = 6): array
    {
        $files = $this->sortBySizeDesc($files);

        $threadsFiles = [];
        $filesSizeInThreads = array_fill(0, $processesCount, 0);
        $relatedItems = [];

        foreach ($files as $file) {
            $entity = $this->getEntityNameToInsert($file);
            if ($entity !== null) {
                $region = $this->getRegionNumberForFile($file);
                $thread = $relatedItems["{$region}_{$entity}"] ?? $this->getThreadIdWithMinSize($filesSizeInThreads);
                $relatedItems["{$region}_{$entity}"] = $thread;
                $filesSizeInThreads[$thread] += $file->getSize();
                $threadsFiles[$thread][] = $file;
            }
        }

        foreach ($files as $file) {
            $entity = $this->getEntityNameToDelete($file);
            if ($entity !== null) {
                $region = $this->getRegionNumberForFile($file);
                $thread = $relatedItems["{$region}_{$entity}"] ?? $this->getThreadIdWithMinSize($filesSizeInThreads);
                $relatedItems["{$region}_{$entity}"] = $thread;
                $filesSizeInThreads[$thread] += $file->getSize();
                $threadsFiles[$thread][] = $file;
            }
        }

        return $threadsFiles;
    }

    /**
     * Сортирует файлы по размеру по убыванию, чтобы было легче балансировать количество данных в потоках.
     *
     * @param UnpackerFile[] $files
     *
     * @return UnpackerFile[]
     */
    private function sortBySizeDesc(array $files): array
    {
        usort(
            $files,
            fn (UnpackerFile $a, UnpackerFile $b): int => $b->getSize() <=> $a->getSize()
        );

        return $files;
    }

    /**
     * Возвращает имя сущности, к которой привязан указанный файл, если такая сущность указана.
     */
    private function getEntityNameToInsert(UnpackerFile $file): ?string
    {
        return $this->entityManager->getDescriptorByInsertFile($file->getName())?->getName();
    }

    /**
     * Возвращает имя сущности, к которой привязан указанный файл, если такая сущность указана.
     */
    private function getEntityNameToDelete(UnpackerFile $file): ?string
    {
        return $this->entityManager->getDescriptorByDeleteFile($file->getName())?->getName();
    }

    /**
     * Возвращает номер региона для указанного имени файла.
     */
    private function getRegionNumberForFile(UnpackerFile $file): ?int
    {
        if (preg_match("#^/?(\d+)/.*#", $file->getName(), $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Возвращает идентификатор трэда с наименьшим размером файлов.
     *
     * @param array<int, int> $filesSizeInThreads
     */
    private function getThreadIdWithMinSize(array $filesSizeInThreads): int
    {
        $minId = 0;
        $minSize = null;
        foreach ($filesSizeInThreads as $id => $size) {
            if ($size === 0) {
                return $id;
            }
            if ($minSize === null || $minSize > $size) {
                $minId = $id;
                $minSize = $size;
            }
        }

        return $minId;
    }
}
