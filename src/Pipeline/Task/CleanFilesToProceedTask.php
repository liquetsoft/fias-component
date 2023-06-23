<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;
use Liquetsoft\Fias\Component\Helper\ArrayHelper;
use Liquetsoft\Fias\Component\Pipeline\PipelineState;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAware;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAwareTrait;
use Marvin255\FileSystemHelper\FileSystemHelper;

/**
 * Задача, которая удаляет файлы после обработки.
 */
final class CleanFilesToProceedTask implements PipelineTaskLogAware
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
        $files = ArrayHelper::ensureArrayElements(
            $state->get(PipelineStateParam::FILES_TO_PROCEED),
            FiasFileSelectorFile::class
        );

        foreach ($files as $file) {
            if ($file->isArchived()) {
                continue;
            }

            $this->logInfo(
                'Removing file',
                [
                    'file' => $file->getPath(),
                ]
            );

            $this->fs->removeIfExists($file->getPath());
        }

        return $state->without(PipelineStateParam::FILES_TO_PROCEED);
    }
}
