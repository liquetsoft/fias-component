<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasThread;

use Liquetsoft\Fias\Component\Exception\FiasThreadException;
use Symfony\Component\Process\Process;

/**
 * Интерфейс для объекта, который создает symfony process.
 *
 * @internal
 */
interface FiasThreadRunnerSymfonyCreator
{
    /**
     * Создает новый объект процесса для указанных параметров.
     *
     * @param iterable<FiasThreadParams> $threads
     *
     * @return Process[]
     *
     * @throws FiasThreadException
     */
    public function create(iterable $threads): array;
}
