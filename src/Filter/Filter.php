<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Filter;

/**
 * Интерфейс для фильтров.
 */
interface Filter
{
    public function test($testData): bool;
}
