<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Psr\Log\LogLevel;

/**
 * Задача, которая получает ссылку на архив с полной версией ФИАС.
 */
class InformFullTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    protected FiasInformer $informer;

    public function __construct(FiasInformer $informer)
    {
        $this->informer = $informer;
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): void
    {
        $info = $this->informer->getCurrentCompleteVersion();

        $this->log(
            LogLevel::INFO,
            "Full version of FIAS is '{$info->getVersion()}' and can be downloaded from '{$info->getUrl()}'.",
            [
                'next_version' => $info->getVersion(),
                'url' => $info->getUrl(),
            ]
        );

        $state->setAndLockParameter(StateParameter::FIAS_INFO, $info);
    }
}
