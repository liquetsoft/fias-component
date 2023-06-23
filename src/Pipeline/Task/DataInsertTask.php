<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasEntity\FiasEntity;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntityRepository;
use Liquetsoft\Fias\Component\Storage\Storage;

/**
 * Задача, которая вставляет данные в чистую БД.
 */
final class DataInsertTask extends DataAbstractTask
{
    /**
     * {@inheritdoc}
     */
    protected function getFiasEntityByFile(\SplFileInfo $file, FiasEntityRepository $enityRepository): ?FiasEntity
    {
        foreach ($enityRepository->getAllEntities() as $entity) {
            if ($entity->isFileNameFitsXmlInsertFileMask($file->getPathname())) {
                return $entity;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function processItem(object $item, Storage $storage): void
    {
        $storage->insert($item);
    }
}
