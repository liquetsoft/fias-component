<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Psr\Log\LogLevel;

/**
 * Задача, которая получает ссылку на архив с обновлениями ФИАС
 * относительно указанной в состоянии версии.
 */
final class InformDeltaTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    public function __construct(private readonly FiasInformer $informer)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): void
    {
        $version = $state->getParameterInt(StateParameter::FIAS_VERSION_NUMBER);
        if ($version <= 0) {
            throw new TaskException('Version parameter must exist and be greater than zero');
        }

        $info = $this->informer->getNextVersion($version);
        if ($info === null) {
            $state->complete();
            $this->log(
                LogLevel::INFO,
                "Current version '{$version}' is up to date",
                [
                    'current_version' => $version,
                ]
            );
        } else {
            $this->log(
                LogLevel::INFO,
                "Current version of FIAS is '{$version}', next version is '{$info->getVersion()}' and can be downloaded from '{$info->getDeltaUrl()}'",
                [
                    'current_version' => $version,
                    'next_version' => $info->getVersion(),
                    'url' => $info->getDeltaUrl(),
                ]
            );
            $state->setAndLockParameter(StateParameter::FIAS_NEXT_VERSION_NUMBER, $info->getVersion());
            $state->setAndLockParameter(StateParameter::FIAS_VERSION_ARCHIVE_URL, $info->getDeltaUrl());
        }
    }
}
