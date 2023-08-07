<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelector;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;
use Liquetsoft\Fias\Component\Pipeline\PipelineState;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAware;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAwareTrait;
use Marvin255\FileSystemHelper\FileSystemHelper;

/**
 * Задача, которая собирает список файлов для загрузки из архива.
 */
final class SelectFilesFromArchiveTask implements PipelineTaskLogAware
{
    use PipelineTaskLogAwareTrait;

    public function __construct(
        private readonly FiasFileSelector $selector,
        private readonly FileSystemHelper $fs
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function run(PipelineState $state): PipelineState
    {
        $path = $state->getString(PipelineStateParam::DOWNLOAD_TO_FILE);
        $fileInfo = $this->fs->makeFileInfo($path);
        $files = $this->selector->select($fileInfo);

        $this->logInfo(
            'Files selected from archive',
            [
                'archive' => $path,
                'files' => array_map(
                    fn (FiasFileSelectorFile $file): string => $file->getPath(),
                    $files
                ),
            ]
        );

        return $state->with(PipelineStateParam::FILES_TO_PROCEED, $files);
    }
}
