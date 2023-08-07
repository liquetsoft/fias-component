<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Downloader\Downloader;
use Liquetsoft\Fias\Component\Pipeline\PipelineState;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAware;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAwareTrait;
use Marvin255\FileSystemHelper\FileSystemHelper;

/**
 * Задача, которая скачивает файл по указанной ссылке.
 */
final class DownloadTask implements PipelineTaskLogAware
{
    use PipelineTaskLogAwareTrait;

    public function __construct(
        private readonly Downloader $downloader,
        private readonly FileSystemHelper $fs
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function run(PipelineState $state): PipelineState
    {
        $url = $state->getString(PipelineStateParam::ARCHIVE_URL);
        $pathToLocalFile = $state->getString(PipelineStateParam::DOWNLOAD_TO_FILE);
        $localFile = $this->fs->makeFileInfo($pathToLocalFile);
        $logParams = [
            'download_url' => $url,
            'download_local_file' => $localFile->getPathname(),
        ];

        $this->logInfo('Downloading started', $logParams);
        $this->downloader->download($url, $localFile);
        $this->logInfo('Downloading completed', $logParams);

        return $state;
    }
}
