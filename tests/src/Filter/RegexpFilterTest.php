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
    public function testTestException(): void
    {
        $filter = new RegexpFilter();

        $this->expectException(\InvalidArgumentException::class);
        $filter->test($filter);
    }

    /**
     * @param string[]      $regexps
     * @param object|string $testedObject
     * @param bool          $result
     *
     * @dataProvider provideTestData
     */
    public function testTest(array $regexps, $testedObject, bool $result): void
    {
        $filter = new RegexpFilter($regexps);
        $testResult = $filter->test($testedObject);

        $this->assertSame($result, $testResult);
    }

    public function provideTestData(): array
    {
        return [
            'positive case' => [
                [
                    '/rts_/',
                    '/_str/',
                ],
                'test_string',
                true,
            ],
            'negative case' => [
                [
                    '/rts_/',
                ],
                'test_string',
                false,
            ],
            'empty case' => [
                [],
                'test_string',
                true,
            ],
            'object case' => [
                [
                    '/rts_/',
                    '/_str/',
                ],
                new ToStringObjectMock('test_string'),
                true,
            ],
        ];
    }
}
