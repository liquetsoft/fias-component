<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Serializer;

use Liquetsoft\Fias\Component\Serializer\FiasSerializerFormat;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для списка форматов, которые поддерживает сериализатор.
 *
 * @internal
 */
final class FiasSerializerFormatTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно сравнит предоставленную строку с форматом.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideIsEqual')]
    public function testIsEqual(mixed $format, bool $expected): void
    {
        $res = FiasSerializerFormat::TEST->isEqual($format);

        $this->assertSame($expected, $res);
    }

    public static function provideIsEqual(): array
    {
        return [
            'correct format' => [
                FiasSerializerFormat::TEST->value,
                true,
            ],
            'correct format in different case' => [
                strtoupper(FiasSerializerFormat::TEST->value),
                true,
            ],
            'correct format with leading spaces' => [
                '       ' . FiasSerializerFormat::TEST->value,
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
