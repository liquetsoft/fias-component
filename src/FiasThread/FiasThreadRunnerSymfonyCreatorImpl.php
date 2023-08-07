<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasThread;

use Liquetsoft\Fias\Component\Exception\FiasThreadException;
use Liquetsoft\Fias\Component\Pipeline\PipelineState;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Объект, который создает symfony process.
 *
 * @internal
 */
final class FiasThreadRunnerSymfonyCreatorImpl implements FiasThreadRunnerSymfonyCreator
{
    private readonly string $pathToBin;

    private readonly string $commandName;

    private readonly SerializerInterface $serializer;

    private readonly PhpExecutableFinder $phpExecutableFinder;

    public function __construct(
        string $pathToBin,
        string $commandName,
        SerializerInterface $serializer,
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

        $this->serializer = $serializer;
        $this->phpExecutableFinder = $phpExecutableFinder ?: new PhpExecutableFinder();
    }

    /**
     * {@inheritdoc}
     */
    public function create(PipelineState $processParams): Process
    {
        $command = [
            $this->getPathToPhp(),
            $this->pathToBin,
            $this->commandName,
        ];
        $input = $this->serializer->serialize(
            $processParams,
            JsonEncoder::FORMAT
        );

        $process = new Process($command);
        $process->setInput($input);
        $process->setTimeout(null);

        return $process;
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
