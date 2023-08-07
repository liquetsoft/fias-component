<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Filter;

use Liquetsoft\Fias\Component\Filter\FilterRegexp;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для фильтра по регулярным выражениям.
 *
 * @internal
 */
class FilterRegexpTest extends BaseCase
{
    /**
     * Проверяет, что объет правильно отфильтрует данные.
     *
     * @psalm-param non-empty-string $regexp
     *
     * @dataProvider provideTestData
     */
    public function testTest(string $regexp, object|string|int $testedObject, bool $result): void
    {
        $filter = new FilterRegexp($regexp);
        $testResult = $filter->test($testedObject);

        $this->assertSame($result, $testResult);
    }

    public function provideTestData(): array
    {
        return [
            'filter is true' => [
                '/_str/',
                'test_string',
                true,
            ],
            'filter is false' => [
                '/rts_/',
                'test_string',
                false,
            ],
            'filter Stringable object' => [
                '/_str/',
                new class() {
                    public function __toString(): string
                    {
                        return 'test_string';
                    }
                },
                true,
            ],
            'filter non Stringable object' => [
                '/_str/',
                $this,
                false,
            ],
            'filter int' => [
                '/12/',
                12345,
                true,
            ],
        ];
    }
}
