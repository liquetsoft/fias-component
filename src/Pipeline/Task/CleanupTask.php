<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\PipelineState;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAware;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAwareTrait;
use Marvin255\FileSystemHelper\FileSystemHelper;
use Psr\Log\LogLevel;

/**
 * Задача, которая очищает временные файлы после работы пайплайна.
 */
final class CleanupTask implements PipelineTaskLogAware
{
    use PipelineTaskLogAwareTrait;

    public function __construct(private readonly FileSystemHelper $fs)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function run(PipelineState $state): PipelineState
    {
        $toRemove = [
            $state->get(PipelineStateParam::DOWNLOAD_TO_FILE),
            $state->get(PipelineStateParam::EXTRACT_TO_FOLDER),
        ];

        foreach ($toRemove as $path) {
            if (\is_string($path)) {
                $this->fs->removeIfExists($path);
                $this->log(LogLevel::INFO, "Path '{$path}' cleaned");
            } elseif ($path instanceof \SplFileInfo) {
                $this->fs->removeIfExists($path);
                $this->log(LogLevel::INFO, "Path '{$path->getPathname()}' cleaned");
            }
        }

        return $state;
    }
}
