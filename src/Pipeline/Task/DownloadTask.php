<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Downloader\Downloader;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Psr\Log\LogLevel;

/**
 * Задача, которая скачивает архив из текущего состояния по ссылке
 * в указанный в состоянии локальный файл.
 */
final class DownloadTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    public function __construct(private readonly Downloader $downloader)
    {
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function run(State $state): State
    {
        $url = $state->getParameterString(StateParameter::FIAS_VERSION_ARCHIVE_URL);
        if ($url === '') {
            throw TaskException::create("Source url isn't set");
        }

        $filePath = $state->getParameterString(StateParameter::PATH_TO_DOWNLOAD_FILE);
        if ($filePath === '') {
            throw TaskException::create("Destination path isn't set");
        }

        $this->log(
            LogLevel::INFO,
            'Downloading file',
            [
                'url' => $url,
                'destination' => $filePath,
            ]
        );

        $this->downloader->download($url, new \SplFileInfo($filePath));

        $this->log(
            LogLevel::INFO,
            'File downloaded',
            [
                'url' => $url,
                'destination' => $filePath,
            ]
        );

        return $state->setParameter(StateParameter::PATH_TO_SOURCE, $filePath);
    }
}
