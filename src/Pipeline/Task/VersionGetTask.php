<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\VersionManager\VersionManager;

/**
 * Задача, которая получает текущую версию ФИАС из менеджера версий.
 */
class VersionGetTask implements Task
{
    protected VersionManager $versionManager;

    public function __construct(VersionManager $versionManager)
    {
        $this->versionManager = $versionManager;
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): void
    {
        $version = $this->versionManager->getCurrentVersion();

        if ($version === null) {
            throw new TaskException('There is no version of FIAS installed.');
        }

        $state->setAndLockParameter(StateParameter::FIAS_VERSION, $version->getVersion());
    }
}
