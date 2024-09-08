<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FilesDispatcher;

use Liquetsoft\Fias\Component\EntityManager\EntityManager;

/**
 * Объект, который разбивает файлы на потоки по именам сущностей, к которым файлы относятся.
 */
class EntityFileDispatcher implements FilesDispatcher
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(array $files, int $processesCount = 6): array
    {
        $filesByEntites = $this->splitFilesByEntites($files);

        $filesByProcesses = [];
        for ($i = 0; $i < $processesCount; ++$i) {
            $filesByProcesses[] = new FilesDispatcherProcess();
        }

        foreach ($filesByEntites as $filesByEntity) {
            $nextProcess = array_reduce(
                $filesByProcesses,
                function (?FilesDispatcherProcess $carry, FilesDispatcherProcess $item): FilesDispatcherProcess {
                    if ($carry === null || $carry->getWeight() > $item->getWeight()) {
                        return $item;
                    } else {
                        return $carry;
                    }
                }
            );
            foreach ($filesByEntity as $file) {
                $nextProcess->addItem($file);
                $nextProcess->addWeight(filesize($file));
            }
        }

        $res = [];
        foreach ($filesByProcesses as $filesByProcess) {
            if ($filesByProcess->getWeight() > 0) {
                $res[] = $filesByProcess->getItems();
            }
        }

        return $res;
    }

    /**
     * Разбивает файлы по сущностям.
     *
     * @param string[] $files
     *
     * @return array<string, array<int, string>>
     */
    private function splitFilesByEntites(array $files): array
    {
        $filesByEntites = [];
        foreach ($files as $file) {
            $fileName = pathinfo($file, \PATHINFO_BASENAME);
            if (file_exists($file) && $descriptor = $this->entityManager->getDescriptorByInsertFile($fileName)) {
                $filesByEntites[$descriptor->getName()][] = $file;
            }
        }
        foreach ($files as $file) {
            $fileName = pathinfo($file, \PATHINFO_BASENAME);
            if (file_exists($file) && $descriptor = $this->entityManager->getDescriptorByDeleteFile($fileName)) {
                $filesByEntites[$descriptor->getName()][] = $file;
            }
        }

        return $filesByEntites;
    }
}
