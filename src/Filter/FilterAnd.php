<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Filter;

/**
 * Фильтр, который соединяет несколько фильтров через AND.
 */
final class FilterAnd implements Filter
{
    /**
     * @param Filter[] $filters
     */
    public function __construct(private readonly array $filters = [])
    {
    }

    /**
     * {@inheritDoc}
     */
    public function test(mixed $testData): bool
    {
        foreach ($this->filters as $filter) {
            if (!$filter->test($testData)) {
                return false;
            }
        }

        return true;
    }
}
