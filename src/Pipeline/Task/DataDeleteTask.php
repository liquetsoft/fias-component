<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Exception\StorageException;
use SplFileInfo;

/**
 * Задача, которая читает данные из xml и удаляет их из БД.
 */
class DataDeleteTask extends DataAbstractTask
{
    /**
     * @inheritdoc
     */
    protected function getDescriptorForFile(SplFileInfo $fileInfo): ?EntityDescriptor
    {
        return $this->entityManager->getDescriptorByDeleteFile($fileInfo->getFilename());
    }

    /**
     * {@inheritdoc}
     *
     * @throws StorageException
     */
    protected function processItem(object $item): void
    {
        $this->storage->delete($item);
    }
}
