<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\FilesDispatcher\FilesDispatcher;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Psr\Log\LogLevel;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Задача, которая распределяет файлы в обработку для symfony/process.
 */
final class ProcessSwitchTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    public function __construct(
        private readonly FilesDispatcher $filesDispatcher,
        private readonly SerializerInterface $serializer,
        private readonly string $pathToBin,
        private readonly string $commandName,
        private readonly int $numberOfParallel = 5,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): State
    {
        $rawFiles = $state->getParameter(StateParameter::FILES_TO_PROCEED);
        $files = array_map(
            fn ($file): string => (string) $file,
            \is_array($rawFiles) ? $rawFiles : []
        );

        $dispatchedFiles = $this->filesDispatcher->dispatch($files, $this->numberOfParallel);

        $processes = $this->createProcessesList($state, $dispatchedFiles);
        $this->runProcesses($processes);

        return $state;
    }

    /**
     * Запускает все процессы и обрабатывает результат.
     *
     * @param Process[] $processes
     */
    private function runProcesses(array $processes): void
    {
        $this->startProcesses($processes);
        $this->log(LogLevel::INFO, 'All processes started');
        $this->waitTillProcessesComplete($processes);
        $this->log(LogLevel::INFO, 'All processes completed');
        $this->handleProcessesResults($processes);
    }

    /**
     * Запускает все процессы асинхронно.
     *
     * @param Process[] $processes
     */
    private function startProcesses(array $processes): void
    {
        foreach ($processes as $process) {
            $process->enableOutput();
            $process->setTimeout(null);
            $process->start();
        }
    }

    /**
     * Цикл, который ждет завершения всех процессов.
     *
     * @param Process[] $processes
     */
    private function waitTillProcessesComplete(array $processes): void
    {
        do {
            sleep(5);
            $isProcessesFinished = true;
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $isProcessesFinished = false;
                    break;
                }
            }
        } while (!$isProcessesFinished);
    }

    /**
     * Обрабатывает результаты всех процессов.
     *
     * @param Process[] $processes
     */
    private function handleProcessesResults(array $processes): void
    {
        foreach ($processes as $process) {
            if (!$process->isSuccessful()) {
                $this->log(
                    LogLevel::ERROR,
                    "Process completed with error: '{$process->getErrorOutput()}'"
                );
            }
        }
    }

    /**
     * Создает список процессов для параллельного запуска.
     *
     * @param string[][] $dispatchedFiles
     *
     * @return Process[]
     */
    private function createProcessesList(State $state, array $dispatchedFiles): array
    {
        $processes = [];

        for ($i = 0; $i < $this->numberOfParallel; ++$i) {
            $dispatchedFilesForProcess = $dispatchedFiles[$i] ?? [];
            if (!empty($dispatchedFilesForProcess)) {
                $processes[] = $this->createProcess($state, $dispatchedFilesForProcess);
            }
        }

        return $processes;
    }

    /**
     * Создает новый процесс для списка файлов.
     *
     * @param string[] $dispatchedFiles
     */
    private function createProcess(State $state, array $dispatchedFiles): Process
    {
        $phpBinaryFinder = new PhpExecutableFinder();
        $phpBinaryPath = $phpBinaryFinder->find();
        $input = $this->serializer->serialize($state, 'json');

        $this->log(
            LogLevel::INFO,
            'Creating new process',
            [
                'files' => $dispatchedFiles,
                'path_to_php' => $phpBinaryPath,
                'path_to_bin' => $this->pathToBin,
                'command' => $this->commandName,
            ]
        );

        $process = new Process(
            [
                $phpBinaryPath,
                $this->pathToBin,
                $this->commandName,
            ]
        );
        $process->setInput($input);

        return $process;
    }
}
