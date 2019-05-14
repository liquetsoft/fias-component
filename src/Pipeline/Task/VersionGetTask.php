<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\VersionManager\VersionManager;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Exception\TaskException;

/**
 * Задача, которая получает текущую версию ФИАС из менеджера версий.
 */
class VersionGetTask implements Task
{
    /**
     * @var VersionManager
     */
    protected $versionManager;

    /**
     * @param VersionManager $versionManager
     */
    public function __construct(VersionManager $versionManager)
    {
        $this->versionManager = $versionManager;
    }

    /**
     * @inheritdoc
     */
    public function run(State $state): void
    {
        $version = $this->versionManager->getCurrentVersion();

        if (!$version->hasResult()) {
            throw new TaskException('There is no version of FIAS installed.');
        }

        $state->setAndLockParameter(Task::FIAS_VERSION_PARAM, $version->getVersion());
    }
}
