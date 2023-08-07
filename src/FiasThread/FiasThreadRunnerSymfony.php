<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasThread;

use Liquetsoft\Fias\Component\Exception\FiasThreadException;
use Symfony\Component\Process\Process;

/**
 * Сервис, который запускает трэды с помощью symfony process.
 */
final class FiasThreadRunnerSymfony implements FiasThreadRunner
{
    private const DEFAULT_SLEEP_TIME = 10;

    public function __construct(
        private readonly FiasThreadRunnerSymfonyCreator $threadCreator,
        /** @psalm-var int<0, max> */
        private readonly int $sleepTime = self::DEFAULT_SLEEP_TIME
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function run(iterable $threads): void
    {
        try {
            $processes = [];
            foreach ($threads as $thread) {
                $processes[] = $this->threadCreator->create($thread);
            }
            $this->startProcesses($processes);
            $this->waitTillProcessesComplete($processes);
            $this->handleProcessesResults($processes);
        } catch (\Throwable $e) {
            throw FiasThreadException::wrap($e);
        }
    }

    /**
     * Запускает все процессы асинхронно.
     *
     * @param Process[] $processes
     */
    private function startProcesses(array $processes): void
    {
        array_walk(
            $processes,
            fn (Process $process): mixed => $process->start()
        );
    }

    /**
     * Цикл, который ждет завершения всех процессов.
     *
     * @param Process[] $processes
     */
    private function waitTillProcessesComplete(array $processes): void
    {
        do {
            sleep($this->sleepTime);
            $isRunning = false;
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $isRunning = true;
                    break;
                }
            }
        } while ($isRunning);
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
                throw FiasThreadException::create(
                    'Process complete with error: %s',
                    $process->getErrorOutput()
                );
            }
        }
    }
}
