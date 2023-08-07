<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Helper;

use Liquetsoft\Fias\Component\Helper\StringHelper;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для класса, который содержит методы для работы со строками.
 *
 * @internal
 */
class StringHelperTest extends BaseCase
{
    /**
     * Проверяет, что метод верно нормализует строку.
     *
     * @dataProvider provideNormalize
     */
    public function testNormalize(mixed $value, string $awaits): void
    {
        $res = StringHelper::normalize($value);

        $this->assertSame($awaits, $res);
    }

    public function provideNormalize(): array
    {
        return [
            'string' => ['test', 'test'],
            'string with whitespaces' => ['  test test  ', 'test test'],
            'string with uppercase' => ['TeSt', 'test'],
            'int' => [123, '123'],
        ];
    }
}
