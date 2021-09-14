<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Filter;

/**
 * Интерфейс для фильтров.
 */
interface Filter
{
    /**
     * @param mixed $testData
     *
     * @return bool
     */
    public function test($testData): bool;
}
