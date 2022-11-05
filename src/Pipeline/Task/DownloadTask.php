<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Downloader\Downloader;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasInformer\InformerResponse;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Psr\Log\LogLevel;
use SplFileInfo;

/**
 * Задача, которая скачивает архив из текущего состояния по ссылке
 * в указанный в состоянии локальный файл.
 */
class DownloadTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    protected Downloader $downloader;

    /**
     * @param Downloader $downloader
     */
    public function __construct(Downloader $downloader)
    {
        $this->downloader = $downloader;
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): void
    {
        $info = $state->getParameter(State::FIAS_INFO_PARAM);
        if (!($info instanceof InformerResponse)) {
            throw new TaskException(
                "State parameter '" . State::FIAS_INFO_PARAM . "' must be an '" . InformerResponse::class . "' instance for '" . self::class . "'."
            );
        }

        $localFile = $state->getParameter(State::DOWNLOAD_TO_FILE_PARAM);
        if (!($localFile instanceof SplFileInfo)) {
            throw new TaskException(
                "State parameter '" . State::DOWNLOAD_TO_FILE_PARAM . "' must be an '" . SplFileInfo::class . "' instance for '" . self::class . "'."
            );
        }

        $this->log(
            LogLevel::INFO,
            "Downloading '{$info->getUrl()}' to '{$localFile->getPathname()}'."
        );

        $this->downloader->download($info->getUrl(), $localFile);
    }
}
