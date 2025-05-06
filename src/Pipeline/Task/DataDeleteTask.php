<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Exception\StorageException;

/**
 * Задача, которая читает данные из xml и удаляет их из БД.
 */
class DataDeleteTask extends DataAbstractTask
{
    /**
     * {@inheritDoc}
     */
    #[\Override]
    protected function getFileDescriptor(\SplFileInfo $file): ?EntityDescriptor
    {
        return $this->entityManager->getDescriptorByDeleteFile($file->getBasename());
    }

    /**
     * {@inheritDoc}
     *
     * @throws StorageException
     */
    #[\Override]
    protected function processItem(object $item): void
    {
        $this->storage->delete($item);
    }
}
