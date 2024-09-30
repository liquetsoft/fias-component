<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Serializer;

use Liquetsoft\Fias\Component\Serializer\FiasFilterEmptyStringsDenormalizer;
use Liquetsoft\Fias\Component\Serializer\FiasSerializerFormat;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Тест для объекта, который преобразует данные из xml ФИАС в объекты.
 *
 * @internal
 */
final class FiasFilterEmptyStringsDenormalizerTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно денормализует данные.
     *
     * @dataProvider provideDenormalize
     */
    public function testDenormalize(mixed $data, string $format, mixed $expected): void
    {
        $denormalizer = new FiasFilterEmptyStringsDenormalizer();

        $res = $denormalizer->denormalize($data, 'test_type', $format);

        $this->assertSame($expected, $res);
    }

    public static function provideDenormalize(): array
    {
        return [
            'xml and array' => [
                ['test' => ''],
                FiasSerializerFormat::XML->value,
                [],
            ],
            'xml and not an array' => [
                'test',
                FiasSerializerFormat::XML->value,
                'test',
            ],
            'xml and array without empty strings' => [
                ['test' => 'qwe'],
                FiasSerializerFormat::XML->value,
                ['test' => 'qwe'],
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
        $format = FiasSerializerFormat::XML->value;
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

        $denormalizer = new FiasFilterEmptyStringsDenormalizer();
        $denormalizer->setDenormalizer($nestedDenormalizer);

        $res = $denormalizer->denormalize($data, $type, $format, $context);

        $this->assertSame($nestedReturn, $res);
    }

    /**
     * Проверяет, что объект првильно определит, что данные могут быть обработаны.
     *
     * @dataProvider provideSupportsDenormalization
     */
    public function testSupportsDenormalization(mixed $data, string $format, bool $expected): void
    {
        $denormalizer = new FiasFilterEmptyStringsDenormalizer();

        $res = $denormalizer->supportsDenormalization($data, 'test_type', $format);

        $this->assertSame($expected, $res);
    }

    public static function provideSupportsDenormalization(): array
    {
        return [
            'xml and array' => [
                ['test' => ''],
                FiasSerializerFormat::XML->value,
                true,
            ],
            'xml and not an array' => [
                'test',
                FiasSerializerFormat::XML->value,
                false,
            ],
            'xml and array without empty strings' => [
                ['test' => 'qwe'],
                FiasSerializerFormat::XML->value,
                false,
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
    public function testGetSupportedTypes(string $format, array $expected): void
    {
        $denormalizer = new FiasFilterEmptyStringsDenormalizer();

        $res = $denormalizer->getSupportedTypes($format);

        $this->assertSame($expected, $res);
    }

    public static function provideGetSupportedTypesXML(): array
    {
        return [
            'xml' => [
                FiasSerializerFormat::XML->value,
                ['*' => false],
            ],
            'json' => [
                'json',
                [],
            ],
        ];
    }
}
