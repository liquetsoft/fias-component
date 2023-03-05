<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Filter;

/**
 * Интерфейс для фильтров.
 */
interface Filter
{
    /**
     * Возвращает правду, если параметр соответствует условиям текущего фильтра.
     */
    public function test(mixed $testData): bool;
}
