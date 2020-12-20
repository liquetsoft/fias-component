<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Exception\StorageException;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use SplFileInfo;

/**
 * Задача, которая читает данные из xml и удаляет их из БД.
 */
class DataDeleteTask extends DataAbstractTask
{
    /**
     * {@inheritDoc}
     */
    protected function getFileNamesFromState(State $state): array
    {
        $fileNames = $state->getParameter(Task::FILES_TO_DELETE_PARAM);

        return is_array($fileNames) ? $fileNames : [];
    }

    /**
     * {@inheritDoc}
     */
    protected function getDescriptorForFile(SplFileInfo $fileInfo): ?EntityDescriptor
    {
        return $this->entityManager->getDescriptorByDeleteFile($fileInfo->getFilename());
    }

    /**
     * {@inheritDoc}
     *
     * @throws StorageException
     */
    protected function processItem(object $item): void
    {
        $this->storage->delete($item);
    }
}
