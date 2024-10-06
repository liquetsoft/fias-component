<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Storage\Storage;
use Psr\Log\LogLevel;

/**
 * Задача, которая очищает хранилища, для всех сущностей, которые привязаны к
 * сущностям ФИАС.
 */
final class TruncateTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly Storage $storage,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): State
    {
        $this->storage->start();
        foreach ($this->entityManager->getBindedClasses() as $className) {
            if (!$this->storage->supportsClass($className)) {
                continue;
            }
            $this->log(
                LogLevel::INFO, "Truncating '{$className}' entity",
                [
                    'entity' => $className,
                ]
            );
            $this->storage->truncate($className);
        }
        $this->storage->stop();

        return $state;
    }
}
