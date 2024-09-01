<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Filter;

/**
 * Фильтр, который проверяет подходит ли указанная строка под одно из регулярных
 * выражения из набора.
 */
class RegexpFilter implements Filter
{
    /**
     * @var string[]
     */
    private array $regexps;

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
    public function test($testData): bool
    {
        if (\is_scalar($testData)) {
            $testData = (string) $testData;
        } elseif (\is_object($testData) && method_exists($testData, '__toString')) {
            $testData = (string) $testData->__toString();
        } else {
            $message = 'This filter supports only strings or objects that can be coverted to strings.';
            throw new \InvalidArgumentException($message);
        }

        if (empty($this->regexps)) {
            return true;
        }

        $isTested = false;
        foreach ($this->regexps as $regexp) {
            if ($regexp !== '' && preg_match($regexp, $testData)) {
                $isTested = true;
                break;
            }
        }

        return $isTested;
    }
}
