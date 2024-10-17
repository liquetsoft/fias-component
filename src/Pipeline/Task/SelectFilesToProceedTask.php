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
        $pathToSource = $state->getParameterString(StateParameter::PATH_TO_SOURCE);
        if ($pathToSource === '') {
            throw TaskException::create("'%s' is not a valid source", $pathToSource);
        }

        $source = new \SplFileInfo($pathToSource);

        $this->log(
            LogLevel::INFO,
            'Selecting files from source',
            [
                'path' => $pathToSource,
            ]
        );

        if ($this->fiasFileSelector->supportSource($source)) {
            $files = $this->fiasFileSelector->selectFiles($source);
        } else {
            $files = [];
        }

        if (\count($files) === 0) {
            $this->log(
                LogLevel::INFO,
                'No files selected from source',
                [
                    'source' => $pathToSource,
                    'files' => 0,
                ]
            );

            return $state->complete();
        }

        $this->log(
            LogLevel::INFO,
            'Files selected from source',
            [
                'source' => $pathToSource,
                'files' => \count($files),
            ]
        );

        return $state->setParameter(StateParameter::FILES_TO_PROCEED, $files);
    }
}
