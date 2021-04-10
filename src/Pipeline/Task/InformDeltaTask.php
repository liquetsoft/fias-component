<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Psr\Log\LogLevel;

/**
 * Задача, которая получает ссылку на архив с обновлениями ФИАС
 * относительно указанной в состоянии версии.
 */
class InformDeltaTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

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
     * {@inheritDoc}
     */
    public function run(State $state): void
    {
        $version = (int) $state->getParameter(Task::FIAS_VERSION_PARAM);
        if (!$version) {
            throw new TaskException(
                "State parameter '" . Task::FIAS_VERSION_PARAM . "' is required for '" . self::class . "'."
            );
        }

        $info = $this->informer->getDeltaInfo($version);
        if (!$info->hasResult()) {
            $state->complete();
            $this->log(
                LogLevel::INFO,
                "Current version '{$version}' is up to date.",
                [
                    'current_version' => $version,
                ]
            );
        } else {
            $this->log(
                LogLevel::INFO,
                "Current version of FIAS is '{$version}', next version is '{$info->getVersion()}' and can be downloaded from '{$info->getUrl()}'.",
                [
                    'current_version' => $version,
                    'next_version' => $info->getVersion(),
                    'url' => $info->getUrl(),
                ]
            );
        }

        $state->setAndLockParameter(Task::FIAS_INFO_PARAM, $info);
    }
}
