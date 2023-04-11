<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasEntity\FiasEntityBinder;
use Liquetsoft\Fias\Component\Pipeline\PipelineState;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAware;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAwareTrait;
use Liquetsoft\Fias\Component\Storage\Storage;

/**
 * Задача, которая очищает все данные для сущностей в хранилище.
 */
final class TrunkateStorageTask implements PipelineTaskLogAware
{
    use PipelineTaskLogAwareTrait;

    public function __construct(
        private readonly FiasEntityBinder $fiasEntityBinder,
        private readonly Storage $storage
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function run(PipelineState $state): PipelineState
    {
        $this->storage->start();
        foreach ($this->fiasEntityBinder->getBindings() as $entityName => $boundClass) {
            if (!$this->storage->supports($boundClass)) {
                continue;
            }
            $this->storage->truncate($boundClass);
            $this->logInfo(
                'Strorage for entity truncated',
                [
                    'entity' => $entityName,
                    'boundClass' => $boundClass,
                ]
            );
        }
        $this->storage->stop();

        return $state;
    }
}
