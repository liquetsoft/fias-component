<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Filter;

use Liquetsoft\Fias\Component\Filter\RegexpFilter;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\Mock\ToStringObjectMock;

/**
 * Тест для фильтра по регулярным выражениям.
 *
 * @internal
 */
class RegexpFilterTest extends BaseCase
{
    /**
     * Проверяет, что объет правильно отфильтрует данные.
     *
     * @param string[] $regexps
     *
     * @dataProvider provideTestData
     */
    public function testTest(array $regexps, object|string|int $testedObject, bool $result): void
    {
        $filter = new RegexpFilter($regexps);
        $testResult = $filter->test($testedObject);

        $this->assertSame($result, $testResult);
    }

    public function provideTestData(): array
    {
        return [
            'filter is true' => [
                [
                    '/rts_/',
                    '/_str/',
                ],
                'test_string',
                true,
            ],
            'filter is false' => [
                [
                    '/rts_/',
                ],
                'test_string',
                false,
            ],
            'empty filters' => [
                [],
                'test_string',
                true,
            ],
            'filter Stringable object' => [
                [
                    '/rts_/',
                    '/_str/',
                ],
                new ToStringObjectMock('test_string'),
                true,
            ],
            'filter non Stringable object' => [
                [
                    '/rts_/',
                    '/_str/',
                ],
                $this,
                false,
            ],
            'filter int' => [
                [
                    '/12/',
                ],
                12345,
                true,
            ],
        ];
    }
}
