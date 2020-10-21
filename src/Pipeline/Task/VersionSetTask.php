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
        $version_data = $state->getParameter(Task::FIAS_INFO_PARAM);
        $size = $state->getParameter(Task::FIAS_SIZE);

        if ($version_data instanceof InformerResponse && $version_data->hasResult()) {
            $this->versionManager->setCurrentVersionData($version_data, $size);
        }
    }
}
