<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasThread;

use Liquetsoft\Fias\Component\Exception\FiasThreadException;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;

/**
 * Интерфейс для объекта, который разбивает список файлов по потокам.
 */
interface FiasThreadPlanner
{
    public const DEFAULT_PROCESS_COUNT = 6;

    /**
     * Разбивает список файлов по потокам.
     *
     * @param FiasFileSelectorFile[] $files
     *
     * @return FiasFileSelectorFile[][]
     *
     * @throws FiasThreadException
     */
    public function plan(array $files, int $processesCount = self::DEFAULT_PROCESS_COUNT): array;
}
