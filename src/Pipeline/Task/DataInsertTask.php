<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Exception\StorageException;
use SplFileInfo;

/**
 * Задача, которая читает данные из xml и вставляет их в БД.
 */
class DataInsertTask extends DataAbstractTask
{
    /**
     * @inheritdoc
     */
    protected function getDescriptorForFile(SplFileInfo $fileInfo): ?EntityDescriptor
    {
        return $this->entityManager->getDescriptorByInsertFile($fileInfo->getFilename());
    }

    /**
     * {@inheritdoc}
     *
     * @throws StorageException
     */
    protected function processItem(object $item): void
    {
        $this->storage->insert($item);
    }
}
