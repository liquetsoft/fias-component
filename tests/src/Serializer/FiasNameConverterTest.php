<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Serializer;

use Liquetsoft\Fias\Component\Serializer\FiasNameConverter;
use Liquetsoft\Fias\Component\Serializer\FiasSerializerFormat;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который преобразует имена их xml.
 *
 * @internal
 */
final class FiasNameConverterTest extends BaseCase
{
    /**
     * Проверяет, что объект верно преобразует имя.
     *
     * @dataProvider provideNormalize
     */
    public function testNormalize(string $name, string $format, string $expected): void
    {
        $converter = new FiasNameConverter();
        $res = $converter->normalize($name, \stdClass::class, $format);

        $this->assertSame($expected, $res);
    }

    public static function provideNormalize(): array
    {
        return [
            'name without @' => [
                'test',
                FiasSerializerFormat::XML->value,
                '@test',
            ],
            'name without @ and with spaces' => [
                '   test   ',
                FiasSerializerFormat::XML->value,
                '@test',
            ],
            'not an xml' => [
                'test',
                'json',
                'test',
            ],
            'name with @' => [
                '@test',
                FiasSerializerFormat::XML->value,
                '@test',
            ],
        ];
    }

    /**
     * Проверяет, что объект верно преобразует имя из XML.
     *
     * @dataProvider provideDenormalize
     */
    public function testDenormalize(string $name, string $format, string $expected): void
    {
        $converter = new FiasNameConverter();
        $res = $converter->denormalize($name, \stdClass::class, $format);

        $this->assertSame($expected, $res);
    }

    public static function provideDenormalize(): array
    {
        return [
            'name without @' => [
                'test',
                FiasSerializerFormat::XML->value,
                'test',
            ],
            'name without @ and with spaces' => [
                '   test   ',
                FiasSerializerFormat::XML->value,
                'test',
            ],
            'not an xml' => [
                '@test',
                'json',
                '@test',
            ],
            'name with @' => [
                '@test',
                FiasSerializerFormat::XML->value,
                'test',
            ],
        ];
    }
}
