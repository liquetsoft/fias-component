<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasThread;

use Liquetsoft\Fias\Component\Exception\FiasThreadException;

/**
 * Интерфейс для объекта, который запускает потоки для установки/обновления ФИАС.
 */
interface FiasThreadRunner
{
    /**
     * Запускает трэд для каждой коллекции параметров в массиве.
     *
     * @param iterable<FiasThreadParams> $threads
     *
     * @throws FiasThreadException
     */
    public function run(iterable $threads): void;
}
