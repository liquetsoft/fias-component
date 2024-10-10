<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelector;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Psr\Log\LogLevel;

/**
 * Задание, которое проверяет все файлы в архиве ФИАС
 * и выбирает только те, которые можно обработать.
 */
final class SelectFilesToProceedTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    public function __construct(private readonly FiasFileSelector $fiasFileSelector)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): State
    {
        $pathToArchive = $state->getParameterString(StateParameter::PATH_TO_DOWNLOAD_FILE);
        $archive = new \SplFileInfo($pathToArchive);

        if ($pathToArchive === '' || !$archive->isFile()) {
            throw TaskException::create("'%s' is not a file", $pathToArchive);
        }

        $this->log(
            LogLevel::INFO,
            'Selecting files from archive',
            [
                'path' => $pathToArchive,
            ]
        );

        $files = $this->fiasFileSelector->selectFiles($archive);

        if (\count($files) === 0) {
            $this->log(
                LogLevel::INFO,
                'No files from archive selected',
                [
                    'archive' => $pathToArchive,
                    'files' => 0,
                ]
            );

            return $state->complete();
        }

        $this->log(
            LogLevel::INFO,
            'Files from archive selected',
            [
                'archive' => $pathToArchive,
                'files' => \count($files),
            ]
        );

        return $state->setParameter(StateParameter::FILES_TO_PROCEED, $files);
    }
}
