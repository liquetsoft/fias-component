<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Filter;

/**
 * Фильтр, который соединяет несколько фильтров через AND.
 */
final class FilterAnd implements Filter
{
    /**
     * @var Filter[]
     */
    private readonly array $filters;

    /**
     * @param Filter[] $filters
     */
    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
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
