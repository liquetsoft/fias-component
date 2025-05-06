<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasInformer\FiasInformerResponseFactory;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\VersionManager\VersionManager;

/**
 * Задача, которая сохраняет текущую версию ФИАС.
 */
final class VersionSetTask implements Task
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
        $version = $state->getParameterInt(StateParameter::FIAS_NEXT_VERSION_NUMBER);

        if ($version > 0) {
            $version = FiasInformerResponseFactory::create(
                $version,
                $state->getParameterString(StateParameter::FIAS_NEXT_VERSION_FULL_URL),
                $state->getParameterString(StateParameter::FIAS_NEXT_VERSION_DELTA_URL)
            );
            $this->versionManager->setCurrentVersion($version);
        }

        return $state;
    }
}
