<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasThread;

use Liquetsoft\Fias\Component\Exception\FiasThreadException;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntity;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntityRepository;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;

/**
 * Объект, который разбивает список файлов по потокам.
 */
final class FiasThreadPlannerImpl implements FiasThreadPlanner
{
    public function __construct(private readonly FiasEntityRepository $repo)
    {
    }

    /**
     * {@inheritDoc}
     *
     * - Все файлы для одной сущности должны быть в одном потоке,
     * - внутри потока сначала должны быть файлы на вставку, затем на удаление,
     * - все потоки должны содержать файлы с приблизительно равным общим размером, чтобы потоки не простаивали.
     */
    public function plan(array $files, int $processesCount = self::DEFAULT_PROCESS_COUNT): array
    {
        if (empty($files)) {
            return [];
        }

        $filesByEntites = $this->groupFilesByEntites($files);

        $threads = [];
        for ($i = 0; $i < $processesCount; $i++) {
            $threads[] = new FiasThreadPlannerThread();
        }

        foreach ($filesByEntites as $filesByEntity) {
            $this->selectThread($threads)->addFiles($filesByEntity);
        }

        return array_filter(
            array_map(
                fn (FiasThreadPlannerThread $thread): array => $thread->getFiles(),
                $threads
            )
        );
    }

    /**
     * Группирует файлы по сущностям и сортирует файлы на вставку на первое место, на удаление - на последнее.
     *
     * @param FiasFileSelectorFile[] $files
     *
     * @return array<string, array<int, FiasFileSelectorFile>>
     */
    private function groupFilesByEntites(array $files): array
    {
        $filesByEntites = [];

        foreach ($files as $file) {
            $entityName = $this->getEntityByInsertFile($file)?->getName();
            $insert = true;
            if ($entityName === null) {
                $entityName = $this->getEntityByDeleteFile($file)?->getName();
                $insert = false;
            }
            if ($entityName === null) {
                continue;
            }
            if (!isset($filesByEntites[$entityName])) {
                $filesByEntites[$entityName] = [];
            }
            if ($insert) {
                array_unshift($filesByEntites[$entityName], $file);
            } else {
                array_push($filesByEntites[$entityName], $file);
            }
        }

        return $filesByEntites;
    }

    /**
     * Выбирает поток с наименьщим размером файлов.
     *
     * @param FiasThreadPlannerThread[] $threads
     */
    private function selectThread(array $threads): FiasThreadPlannerThread
    {
        $selectedThread = null;
        $lowestSize = null;
        foreach ($threads as $thread) {
            if ($lowestSize === null || $lowestSize > $thread->getSize()) {
                $selectedThread = $thread;
                $lowestSize = $thread->getSize();
            }
        }

        if ($selectedThread === null) {
            throw FiasThreadException::create('processesCount has to be more than 0');
        }

        return $selectedThread;
    }

    /**
     * Пробует найти первую сущность, для которой указанный файл подходит под шаблон для вставки.
     */
    private function getEntityByInsertFile(FiasFileSelectorFile $file): ?FiasEntity
    {
        $entites = $this->repo->getAllEntities();

        foreach ($entites as $entity) {
            if ($entity->isFileNameFitsXmlInsertFileMask($file->getPath())) {
                return $entity;
            }
        }

        return null;
    }

    /**
     * Пробует найти первую сущность, для которой указанный файл подходит под шаблон для удаления.
     */
    private function getEntityByDeleteFile(FiasFileSelectorFile $file): ?FiasEntity
    {
        $entites = $this->repo->getAllEntities();

        foreach ($entites as $entity) {
            if ($entity->isFileNameFitsXmlDeleteFileMask($file->getPath())) {
                return $entity;
            }
        }

        return null;
    }
}
