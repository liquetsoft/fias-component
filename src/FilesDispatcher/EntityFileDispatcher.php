<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FilesDispatcher;

use Liquetsoft\Fias\Component\EntityManager\EntityManager;

/**
 * Объект, который разбивает файлы на потоки по именам сущностей, к которым файлы относятся.
 *
 * В конуструкторе нужно указать какие именно сущности должны быть распределены по процессам равномерно.
 * Объект постарается не хранит в одном потоке несколько таких файлов. Прежде всего нужно разбивать таким
 * образом именно самые крупные файлы.
 *
 * Остальные файлы будут равномерно распределены по потокам.
 */
class EntityFileDispatcher implements FilesDispatcher
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var string[]
     */
    protected $entitiesToParallel;

    /**
     * @param EntityManager $entityManager
     * @param array         $entitiesToParallel
     */
    public function __construct(EntityManager $entityManager, array $entitiesToParallel = [])
    {
        $this->entityManager = $entityManager;
        $this->entitiesToParallel = $entitiesToParallel;
    }

    /**
     * @inheritDoc
     */
    public function dispatchInsert(array $filesToInsert, int $processesCount = 6): array
    {
        $filesByEntities = $this->getInsertFilesByEntities($filesToInsert);

        return $this->dispatch($filesByEntities, $processesCount);
    }

    /**
     * @inheritDoc
     */
    public function dispatchDelete(array $filesToDelete, int $processesCount = 6): array
    {
        $filesByEntities = $this->getDeleteFilesByEntities($filesToDelete);

        return $this->dispatch($filesByEntities, $processesCount);
    }

    /**
     * Преобразует массив файлов для вставки в массив вида "имя сущности => имя файла".
     *
     * @param string[] $filesToInsert
     *
     * @return array
     */
    protected function getInsertFilesByEntities(array $filesToInsert): array
    {
        $filesByEntities = [];

        foreach ($filesToInsert as $fileToInsert) {
            $descriptor = $this->entityManager->getDescriptorByInsertFile($fileToInsert);
            if ($descriptor) {
                $filesByEntities[$descriptor->getName()] = $fileToInsert;
            }
        }

        return $filesByEntities;
    }

    /**
     * Преобразует массив файлов для удаления в массив вида "имя сущности => имя файла".
     *
     * @param string[] $filesToDelete
     *
     * @return array
     */
    protected function getDeleteFilesByEntities(array $filesToDelete): array
    {
        $filesByEntities = [];

        foreach ($filesToDelete as $fileToDelete) {
            $descriptor = $this->entityManager->getDescriptorByDeleteFile($fileToDelete);
            if ($descriptor) {
                $filesByEntities[$descriptor->getName()] = $fileToDelete;
            }
        }

        return $filesByEntities;
    }

    /**
     * Распределяет файлы по потокам.
     *
     * @param array<string, string> $filesByEntities
     * @param int                   $processesCount
     *
     * @return string[][]
     */
    protected function dispatch(array $filesByEntities, int $processesCount): array
    {
        $dispatched = [];

        $currentProcess = 0;
        foreach ($this->entitiesToParallel as $entityToParallel) {
            if (isset($filesByEntities[$entityToParallel])) {
                $dispatched[$currentProcess][] = $filesByEntities[$entityToParallel];
                unset($filesByEntities[$entityToParallel]);
                ++$currentProcess;
                if ($currentProcess >= $processesCount) {
                    $currentProcess = 0;
                }
            }
        }

        $currentProcess = 0;
        foreach ($filesByEntities as $files) {
            $dispatched[$currentProcess][] = $files;
            ++$currentProcess;
            if ($currentProcess >= $processesCount) {
                $currentProcess = 0;
            }
        }

        return $dispatched;
    }
}
