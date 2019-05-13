<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\Pipeline\State\State;

/**
 * Задача, которая очищает хранилища, для всех сущностей, которые привязаны к
 * сущностям ФИАС.
 */
class TruncateTask implements Task
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @param EntityManager $entityManager
     * @param Storage       $storage
     */
    public function __construct(EntityManager $entityManager, Storage $storage)
    {
        $this->entityManager = $entityManager;
        $this->storage = $storage;
    }

    /**
     * @inheritdoc
     */
    public function run(State $state): void
    {
        $this->storage->start();
        foreach ($this->entityManager->getBindedClasses() as $className) {
            $this->storage->truncate($className);
        }
        $this->storage->stop();
    }
}
