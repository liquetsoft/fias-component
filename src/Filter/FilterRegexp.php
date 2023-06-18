<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Filter;

/**
 * Фильтр, который проверяет подходит ли указанная строка под указанное регулярное выражение.
 */
final class FilterRegexp implements Filter
{
    public function __construct(private readonly string $regexp)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function test(mixed $testData): bool
    {
        if (\is_scalar($testData)) {
            $testData = (string) $testData;
        } elseif ($testData instanceof \Stringable) {
            $testData = $testData->__toString();
        } else {
            return false;
        }

        return preg_match($this->regexp, $testData) === 1;
    }
}
