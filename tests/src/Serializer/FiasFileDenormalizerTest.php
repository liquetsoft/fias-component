<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Serializer;

use Liquetsoft\Fias\Component\FiasFile\FiasFile;
use Liquetsoft\Fias\Component\FiasFile\FiasFileImpl;
use Liquetsoft\Fias\Component\Serializer\FiasFileDenormalizer;
use Liquetsoft\Fias\Component\Serializer\FiasSerializerFormat;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

/**
 * Тест для объекта, который преобразует массив в объект файла.
 *
 * @internal
 */
final class FiasFileDenormalizerTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно денормализует данные.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideDenormalize')]
    public function testDenormalize(mixed $data, array|\Exception $expected): void
    {
        $denormalizer = new FiasFileDenormalizer();

        if ($expected instanceof \Exception) {
            $this->expectExceptionObject($expected);
        }

        $res = $denormalizer->denormalize($data, FiasFile::class);

        if (!($expected instanceof \Exception)) {
            $this->assertInstanceOf(FiasFile::class, $res);
            $this->assertSame(
                $expected,
                [
                    'name' => $res->getName(),
                    'size' => $res->getSize(),
                ]
            );
        }
    }

    public static function provideDenormalize(): array
    {
        return [
            'correct params' => [
                [
                    'name' => 'test.txt',
                    'size' => 213,
                ],
                [
                    'name' => 'test.txt',
                    'size' => 213,
                ],
            ],
            'no name' => [
                [
                    'size' => 213,
                ],
                new InvalidArgumentException("'name' param isn't set"),
            ],
        ];
    }

    /**
     * Проверяет, что объект првильно определит, что данные могут быть обработаны.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideSupportsDenormalization')]
    public function testSupportsDenormalization(string $type, bool $expected): void
    {
        $denormalizer = new FiasFileDenormalizer();

        $res = $denormalizer->supportsDenormalization([], $type, 'json', []);

        $this->assertSame($expected, $res);
    }

    public static function provideSupportsDenormalization(): array
    {
        return [
            'implementation' => [
                FiasFileImpl::class,
                true,
            ],
            'random class' => [
                \stdClass::class,
                false,
            ],
            'random string' => [
                'test',
                false,
            ],
        ];
    }

    /**
     * Проверяет, что объект вернет корректный список поддерживаемых типов.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideGetSupportedTypes')]
    public function testGetSupportedTypes(string $format, array $expected): void
    {
        $denormalizer = new FiasFileDenormalizer();

        $res = $denormalizer->getSupportedTypes($format);

        $this->assertSame($expected, $res);
    }

    public static function provideGetSupportedTypes(): array
    {
        return [
            'xml' => [
                FiasSerializerFormat::XML->value,
                [
                    FiasFileImpl::class => true,
                ],
            ],
            'json' => [
                'json',
                [
                    FiasFileImpl::class => true,
                ],
            ],
        ];
    }
}
