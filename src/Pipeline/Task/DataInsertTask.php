<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\StorageException;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntity;

/**
 * Задача, которая читает данные из xml и вставляет их в БД.
 */
class DataInsertTask extends DataAbstractTask
{
    /**
     * {@inheritDoc}
     */
    protected function getFileDescriptor(\SplFileInfo $file): ?FiasEntity
    {
        return $this->entityManager->getDescriptorByInsertFile($file->getBasename());
    }

    /**
     * {@inheritDoc}
     *
     * @throws StorageException
     */
    protected function processItem(object $item): void
    {
        $this->storage->insert($item);
    }
}
