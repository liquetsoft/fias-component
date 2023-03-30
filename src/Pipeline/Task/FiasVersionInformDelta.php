<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\PipelineException;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\Pipeline\PipelineState;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAware;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAwareTrait;
use Psr\Log\LogLevel;

/**
 * Задача, которая получает информацию о пследующем обновлении ФИАС.
 */
final class FiasVersionInformDelta implements PipelineTaskLogAware
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
        $installedVersion = $state->get(PipelineStateParam::INSTALLED_VERSION);
        if (!\is_int($installedVersion) || $installedVersion <= 0) {
            throw PipelineException::create("Current installed version wasn't provided. Can't find delta");
        }

        $version = $this->informer->getNextVersion($installedVersion);

        if ($version === null) {
            $this->log(LogLevel::INFO, 'Current version is up to date');

            return $state->with(PipelineStateParam::INTERRUPT_PIPELINE, true);
        }

        $versionArray = [
            PipelineStateParam::ARCHIVE_URL->value => $version->getDeltaUrl(),
            PipelineStateParam::PROCESSING_VERSION->value => $version->getVersion(),
            PipelineStateParam::INSTALLED_VERSION->value => $installedVersion,
        ];

        $this->log(
            LogLevel::INFO,
            'Delta version was found',
            $versionArray
        );

        return $state->withList($versionArray);
    }
}
