<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasThread;

use Liquetsoft\Fias\Component\Exception\FiasThreadException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Объект, который создает symfony process.
 *
 * @internal
 */
final class FiasThreadRunnerSymfonyCreatorImpl implements FiasThreadRunnerSymfonyCreator
{
    private readonly string $pathToBin;

    private readonly string $commandName;

    private readonly PhpExecutableFinder $phpExecutableFinder;

    public function __construct(
        string $pathToBin,
        string $commandName,
        PhpExecutableFinder $phpExecutableFinder = null
    ) {
        $this->pathToBin = trim($pathToBin);
        if ($this->pathToBin === '') {
            throw FiasThreadException::create("Path to bin can't be empty");
        }

        $this->commandName = trim($commandName);
        if ($this->commandName === '') {
            throw FiasThreadException::create("Command name can't be empty");
        }

        $this->phpExecutableFinder = $phpExecutableFinder ?: new PhpExecutableFinder();
    }

    /**
     * {@inheritdoc}
     */
    public function create(iterable $threads): array
    {
        $pathToPhp = $this->getPathToPhp();
        $processes = [];
        foreach ($threads as $thread) {
            $process = new Process(
                [
                    $pathToPhp,
                    $this->pathToBin,
                    $this->commandName,
                ]
            );
            $process->setInput(json_encode($thread->all()));
            $process->setTimeout(null);
            $processes[] = $process;
        }

        return $processes;
    }

    /**
     * Возвращает путь до бинарника php.
     */
    private function getPathToPhp(): string
    {
        $pathToPhp = $this->phpExecutableFinder->find();
        if ($pathToPhp === false) {
            throw FiasThreadException::create("Can't find php binary");
        }

        return $pathToPhp;
    }
}
