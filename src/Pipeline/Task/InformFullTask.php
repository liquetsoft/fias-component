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
final class InformFullTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    public function __construct(private readonly FiasInformer $informer)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): State
    {
        $info = $this->informer->getLatestVersion();

        $this->log(
            LogLevel::INFO,
            "Full version of FIAS is '{$info->getVersion()}' and can be downloaded from '{$info->getFullUrl()}'",
            [
                'next_version' => $info->getVersion(),
                'url' => $info->getFullUrl(),
            ]
        );

        return $state->setParameter(StateParameter::FIAS_NEXT_VERSION_NUMBER, $info->getVersion())
            ->setParameter(StateParameter::FIAS_NEXT_VERSION_FULL_URL, $info->getFullUrl())
            ->setParameter(StateParameter::FIAS_NEXT_VERSION_DELTA_URL, $info->getDeltaUrl())
            ->setParameter(StateParameter::FIAS_VERSION_ARCHIVE_URL, $info->getFullUrl());
    }
}
