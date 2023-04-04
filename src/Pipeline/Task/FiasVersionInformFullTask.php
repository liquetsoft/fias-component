<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\Pipeline\PipelineState;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAware;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAwareTrait;

/**
 * Задача, которая получает информацию о полной версии ФИАС.
 */
final class FiasVersionInformFullTask implements PipelineTaskLogAware
{
    use PipelineTaskLogAwareTrait;

    public function __construct(private readonly FiasInformer $informer)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function run(PipelineState $state): PipelineState
    {
        $version = $this->informer->getLatestVersion();
        $versionArray = [
            PipelineStateParam::ARCHIVE_URL->value => $version->getFullUrl(),
            PipelineStateParam::PROCESSING_VERSION->value => $version->getVersion(),
        ];

        $this->logInfo('Full version was found', $versionArray);

        return $state->withList($versionArray);
    }
}
