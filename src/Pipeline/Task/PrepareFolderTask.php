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
 * Задача, которая подготавливает каталог для загрузки и распаковки ФИАС.
 */
final class PrepareFolderTask implements PipelineTaskLogAware
{
    use PipelineTaskLogAwareTrait;

    public function __construct(
        private readonly FileSystemHelper $fs,
        private readonly \SplFileInfo $folder
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function run(PipelineState $state): PipelineState
    {
        $this->log(
            LogLevel::INFO,
            "Preparing temporary folder '{$this->folder->getPathname()}'"
        );

        $this->fs->mkdirIfNotExist($this->folder);
        $this->fs->emptyDir($this->folder);

        $downloadToFile = "{$this->folder->getRealPath()}/archive";
        $extractToFolder = "{$this->folder->getRealPath()}/extracted";

        $this->fs->mkdir($extractToFolder);

        return $state->withList(
            [
                PipelineStateParam::DOWNLOAD_TO_FILE->value => $downloadToFile,
                PipelineStateParam::EXTRACT_TO_FOLDER->value => $extractToFolder,
            ]
        );
    }
}
