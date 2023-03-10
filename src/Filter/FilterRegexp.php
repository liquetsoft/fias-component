<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Filter;

/**
 * Фильтр, который проверяет подходит ли указанная строка под одно из регулярных
 * выражения из набора.
 */
final class FilterRegexp implements Filter
{
    /**
     * @var string[]
     */
    private readonly array $regexps;

    /**
     * @param string[] $regexps
     */
    public function __construct(array $regexps = [])
    {
        $this->regexps = $regexps;
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

        if (empty($this->regexps)) {
            return true;
        }

        foreach ($this->regexps as $regexp) {
            if (preg_match($regexp, $testData)) {
                return true;
            }
        }

        return false;
    }
}
