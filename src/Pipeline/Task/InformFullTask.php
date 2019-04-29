<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Exception\TaskException;

/**
 * Задача, которая получает ссылку на архив с полной версией ФИАС.
 */
class InformFullTask implements Task
{
    /**
     * @var FiasInformer
     */
    protected $informer;

    /**
     * @param FiasInformer $informer
     */
    public function __construct(FiasInformer $informer)
    {
        $this->informer = $informer;
    }

    /**
     * @inheritdoc
     */
    public function run(State $state): void
    {
        $info = $this->informer->getCompleteInfo();

        if (!$info->hasResult()) {
            throw new TaskException("Can't find full archive for fias.");
        }

        $state->setAndLockParameter('fiasInfo', $info);
    }
}
