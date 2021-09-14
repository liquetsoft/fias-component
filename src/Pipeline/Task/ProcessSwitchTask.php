<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\FilesDispatcher\FilesDispatcher;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Psr\Log\LogLevel;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Задача, которая распределяет файлы в обработку для symfony/process.
 */
class ProcessSwitchTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    /**
     * @var FilesDispatcher
     */
    protected $filesDispatcher;

    /**
     * @var string
     */
    protected $pathToBin;

    /**
     * @var string
     */
    protected $commandName;

    /**
     * @var int
     */
    protected $numberOfParallel;

    /**
     * @param FilesDispatcher $filesDispatcher
     * @param string          $pathToBin
     * @param string          $commandName
     * @param int             $numberOfParallel
     */
    public function __construct(
        FilesDispatcher $filesDispatcher,
        string $pathToBin,
        string $commandName,
        int $numberOfParallel = 5
    ) {
        $this->filesDispatcher = $filesDispatcher;
        $this->pathToBin = $pathToBin;
        $this->commandName = $commandName;
        $this->numberOfParallel = $numberOfParallel;
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): void
    {
        $files = $state->getParameter(Task::FILES_TO_PROCEED);
        $files = \is_array($files) ? $files : [];

        $dispatchedFiles = $this->filesDispatcher->dispatch($files, $this->numberOfParallel);

        $processes = $this->createProcessesList($dispatchedFiles);
        $this->runProcesses($processes);
    }

    /**
     * Запускает все процессы и обрабатывает результат.
     *
     * @param Process[] $processes
     */
    protected function runProcesses(array $processes): void
    {
        $this->startProcesses($processes);
        $this->log(LogLevel::INFO, 'All process started.');
        $this->waitTillProcessesComplete($processes);
        $this->log(LogLevel::INFO, 'All process completed.');
        $this->handleProcessesResults($processes);
    }

    /**
     * Запускает все процессы асинхронно.
     *
     * @param Process[] $processes
     */
    protected function startProcesses(array $processes): void
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
    protected function waitTillProcessesComplete(array $processes): void
    {
        do {
            sleep(1);
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
    protected function handleProcessesResults(array $processes): void
    {
        foreach ($processes as $process) {
            if (!$process->isSuccessful()) {
                $this->log(
                    LogLevel::ERROR,
                    'Process complete with error: ' . $process->getErrorOutput()
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
    protected function createProcessesList(array $dispatchedFiles): array
    {
        $processes = [];

        for ($i = 0; $i < $this->numberOfParallel; ++$i) {
            $dispatchedFilesForProcess = $dispatchedFiles[$i] ?? [];
            if (!empty($dispatchedFilesForProcess)) {
                $processes[] = $this->createProcess($dispatchedFilesForProcess);
            }
        }

        return $processes;
    }

    /**
     * Создает новый процесс для списка файлов.
     *
     * @param string[] $dispatchedFiles
     *
     * @return Process
     */
    protected function createProcess(array $dispatchedFiles): Process
    {
        $phpBinaryFinder = new PhpExecutableFinder();
        $phpBinaryPath = $phpBinaryFinder->find();

        $this->log(
            LogLevel::INFO,
            'Creating new process.',
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
        $process->setInput(json_encode($dispatchedFiles));

        return $process;
    }
}
