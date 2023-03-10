<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;

/**
 * Объект, который распаковывает файлы из zip архива.
 */
final class UnpackerZip implements Unpacker
{
    /**
     * {@inheritDoc}
     */
    public function unpack(\SplFileInfo $archive, \SplFileInfo $destination): void
    {
        $zip = $this->openArchive($archive);

        try {
            $unpackResult = $zip->extractTo($destination->getPathName());
            if (!$unpackResult) {
                throw UnpackerException::create(
                    "Can't unpack archive '%s'",
                    $archive->getPathname()
                );
            }
        } catch (\Throwable $e) {
            throw UnpackerException::wrap($e);
        } finally {
            $zip->close();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getListOfFiles(\SplFileInfo $archive): array
    {
        $zip = $this->openArchive($archive);
        $files = array_filter(
            $this->getListOfEntities($zip),
            fn (UnpackerEntity $entity): bool => $entity->isFile()
        );
        $zip->close();

        return array_values($files);
    }

    /**
     * {@inheritDoc}
     */
    public function extractEntity(\SplFileInfo $archive, string $entityName, \SplFileInfo $destination): string
    {
        $path = '';
        $zip = $this->openArchive($archive);

        try {
            $entity = $this->getEntityByName($zip, $entityName);
            if ($entity === null) {
                throw UnpackerException::create(
                    "Can't find entity '%s' in archive '%s'",
                    $entityName,
                    $archive->getPathname()
                );
            }
            $unpackResult = $zip->extractTo($destination->getPathname(), $entity->getName());
            if (!$unpackResult) {
                throw UnpackerException::create(
                    "Can't extract entity '%s' form archive '%s'",
                    $entityName,
                    $archive->getPathname()
                );
            }
            $path = $destination->getPathname() . '/' . $entity->getName();
        } catch (\Throwable $e) {
            throw UnpackerException::wrap($e);
        } finally {
            $zip->close();
        }

        return $path;
    }

    /**
     * Открывает архи и возвращает объект архива.
     */
    private function openArchive(\SplFileInfo $archive): \ZipArchive
    {
        $filePath = $archive->getPathName();
        $zip = new \ZipArchive();

        if ($zip->open($filePath) !== true) {
            throw UnpackerException::create(
                "Can't open '%s' zip archive",
                $filePath
            );
        }

        return $zip;
    }

    /**
     * Возвращает список все сущностей в архиве.
     *
     * @return UnpackerEntity[]
     */
    private function getListOfEntities(\ZipArchive $zipArchive): array
    {
        $listOfFiles = [];
        for ($i = 0; $i < $zipArchive->numFiles; $i++) {
            $stats = $zipArchive->statIndex($i);
            $listOfFiles[] = new UnpackerEntity($stats);
        }

        return $listOfFiles;
    }

    /**
     * Пробует найти сущность в архива по указанному имени.
     */
    private function getEntityByName(\ZipArchive $zipArchive, string $entityName): ?UnpackerEntity
    {
        $entities = array_filter(
            $this->getListOfEntities($zipArchive),
            fn (UnpackerEntity $entity): bool => $entity->getName() === $entityName
        );

        $key = array_key_first($entities);

        return $key ? $entities[$key] : null;
    }
}
