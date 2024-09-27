<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Serializer;

use Liquetsoft\Fias\Component\Serializer\FilterEmptyStringsDenormalizer;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Тест для объекта, который преобразует данные из xml ФИАС в объекты.
 *
 * @internal
 */
final class FilterEmptyStringsDenormalizerTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно денормализует данные.
     *
     * @dataProvider provideDenormalize
     */
    public function testDenormalize(mixed $data, mixed $format, mixed $expected): void
    {
        $denormalizer = new FilterEmptyStringsDenormalizer();

        $res = $denormalizer->denormalize($data, 'test_type', $format);

        $this->assertSame($expected, $res);
    }

    public static function provideDenormalize(): array
    {
        return [
            'xml and array' => [
                ['test' => ''],
                'xml',
                [],
            ],
            'xml and not an array' => [
                'test',
                'xml',
                'test',
            ],
            'xml and array without empty strings' => [
                ['test' => 'qwe'],
                'xml',
                ['test' => 'qwe'],
            ],
            'XML and array' => [
                ['test' => ''],
                'XML',
                [],
            ],
            'json' => [
                ['test' => ''],
                'json',
                ['test' => ''],
            ],
        ];
    }

    /**
     * Проверяет, что объект передаст управление вложенному денормалайзеру.
     */
    public function testDenormalizeAware(): void
    {
        $data = ['test_key_data' => 'test_value_data'];
        $type = 'test_type';
        $format = 'test_format';
        $context = ['test_key_context' => 'test_value_context'];
        $nestedReturn = 'test_return';

        $nestedDenormalizer = $this->mock(DenormalizerInterface::class);
        $nestedDenormalizer->expects($this->once())
            ->method('denormalize')
            ->with(
                $this->identicalTo($data),
                $this->identicalTo($type),
                $this->identicalTo($format),
                $this->identicalTo($context)
            )
            ->willReturn($nestedReturn);

        $denormalizer = new FilterEmptyStringsDenormalizer();
        $denormalizer->setDenormalizer($nestedDenormalizer);

        $res = $denormalizer->denormalize($data, $type, $format, $context);

        $this->assertSame($nestedReturn, $res);
    }

    /**
     * Проверяет, что объект првильно определит, что данные могут быть обработаны.
     *
     * @dataProvider provideSupportsDenormalization
     */
    public function testSupportsDenormalization(mixed $data, mixed $format, bool $expected): void
    {
        $denormalizer = new FilterEmptyStringsDenormalizer();

        $res = $denormalizer->supportsDenormalization($data, 'test_type', $format);

        $this->assertSame($expected, $res);
    }

    public static function provideSupportsDenormalization(): array
    {
        return [
            'xml and array' => [
                ['test' => ''],
                'xml',
                true,
            ],
            'xml and not an array' => [
                'test',
                'xml',
                false,
            ],
            'xml and array without empty strings' => [
                ['test' => 'qwe'],
                'xml',
                false,
            ],
            'XML and array' => [
                ['test' => ''],
                'XML',
                true,
            ],
            'json' => [
                ['test' => ''],
                'json',
                false,
            ],
        ];
    }

    /**
     * Проверяет, что объект вернет корректный список поддерживаемых типов.
     *
     * @dataProvider provideGetSupportedTypesXML
     */
    public function testGetSupportedTypes(mixed $format, array $expected): void
    {
        $denormalizer = new FilterEmptyStringsDenormalizer();

        $res = $denormalizer->getSupportedTypes($format);

        $this->assertSame($expected, $res);
    }

    public static function provideGetSupportedTypesXML(): array
    {
        return [
            'xml' => [
                'xml',
                ['*' => true],
            ],
            'XML' => [
                'XML',
                ['*' => true],
            ],
            'json' => [
                'json',
                [],
            ],
        ];
    }
}