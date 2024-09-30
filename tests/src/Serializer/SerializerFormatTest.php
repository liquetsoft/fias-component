<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Serializer;

use Liquetsoft\Fias\Component\Serializer\SerializerFormat;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для списка форматов, которые поддерживает сериализатор.
 *
 * @internal
 */
final class SerializerFormatTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно сравнит предоставленную строку с форматом.
     *
     * @dataProvider provideIsEqual
     */
    public function testIsEqual(mixed $format, bool $expected): void
    {
        $res = SerializerFormat::TEST->isEqual($format);

        $this->assertSame($expected, $res);
    }

    public static function provideIsEqual(): array
    {
        return [
            'correct format' => [
                SerializerFormat::TEST->value,
                true,
            ],
            'correct format in different case' => [
                strtoupper(SerializerFormat::TEST->value),
                true,
            ],
            'correct format with leading spaces' => [
                '       ' . SerializerFormat::TEST->value,
                true,
            ],
            'incorrect format' => [
                'incorrect format',
                false,
            ],
            'not a string format' => [
                123,
                false,
            ],
        ];
    }
}
