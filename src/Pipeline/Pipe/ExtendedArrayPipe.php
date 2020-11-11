<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Pipe;

use Liquetsoft\Fias\Component\Pipeline\Task\Task;

/**
 * Расширенный объект, который содержит внутренний массив со списком операций для исполнения.
 */
class ExtendedArrayPipe extends ArrayPipe
{
    /**
     * @return Task[]
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @return Task|null
     */
    public function getCleanupTask()
    {
        return $this->cleanupTask;
    }
}
