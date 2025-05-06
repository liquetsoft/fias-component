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
final class VersionGetTask implements Task
{
    public function __construct(private readonly VersionManager $versionManager)
    {
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function run(State $state): State
    {
        $version = $this->versionManager->getCurrentVersion();

        if ($version === null) {
            throw TaskException::create('There is no version of FIAS installed');
        }

        return $state->setParameter(StateParameter::FIAS_VERSION_NUMBER, $version->getVersion());
    }
}
