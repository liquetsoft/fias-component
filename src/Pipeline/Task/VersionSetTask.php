<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasInformer\InformerResponse;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\VersionManager\VersionManager;

/**
 * Задача, которая сохраняет текущую версию ФИАС.
 */
class VersionSetTask implements Task
{
    protected VersionManager $versionManager;

    /**
     * @param VersionManager $versionManager
     */
    public function __construct(VersionManager $versionManager)
    {
        $this->versionManager = $versionManager;
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): void
    {
        $version = $state->getParameter(State::FIAS_INFO_PARAM);

        if ($version instanceof InformerResponse && $version->hasResult()) {
            $this->versionManager->setCurrentVersion($version);
        }
    }
}
