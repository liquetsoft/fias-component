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
class InformDeltaTask implements Task, LoggableTask
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
     * @inheritdoc
     */
    public function run(State $state): void
    {
        $type = $state->getParameter(Task::DOWNLOAD_FILE_TYPE);
        if (!$type) {
            throw new TaskException(
                "State parameter '" . Task::DOWNLOAD_FILE_TYPE . "' is required for '" . self::class . "'."
            );
        }
        $version = (int) $state->getParameter(Task::FIAS_VERSION_PARAM);
        if (!$version) {
            throw new TaskException(
                "State parameter '" . Task::FIAS_VERSION_PARAM . "' is required for '" . self::class . "'."
            );
        }

        $info = $this->informer->getDeltaInfo($version, $type);
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
                "Current version of FIAS is '{$version}', next version is '{$info->getVersion()}' and can be donwloaded from '{$info->getUrl()}'.",
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
