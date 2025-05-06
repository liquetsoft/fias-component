<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Serializer;

use Liquetsoft\Fias\Component\Serializer\FiasSerializerFormat;
use Liquetsoft\Fias\Component\Serializer\FiasUnpackerFileNormalizer;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFile;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFileFactory;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFileImpl;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

/**
 * Тест для объекта, который преобразует объект файла из архива в массив.
 *
 * @internal
 */
final class FiasUnpackerFileNormalizerTest extends BaseCase
{
    /**
     * Проверяет, что объект верно преобразует состояние.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideNormalize')]
    public function testNormalize(object $object, array|\Exception $expected): void
    {
        $normalizer = new FiasUnpackerFileNormalizer();

        if ($expected instanceof \Exception) {
            $this->expectExceptionObject($expected);
        }

        $res = $normalizer->normalize($object, 'json', []);

        if (!($expected instanceof \Exception)) {
            $this->assertSame($expected, $res);
        }
    }

    public static function provideNormalize(): array
    {
        return [
            'wrong object type exception' => [
                new \stdClass(),
                new InvalidArgumentException(UnpackerFile::class),
            ],
            'file' => [
                UnpackerFileFactory::create(
                    new \SplFileInfo('/test'),
                    'test.txt',
                    10,
                    20
                ),
                [
                    'archiveFile' => '/test',
                    'name' => 'test.txt',
                    'index' => 10,
                    'size' => 20,
                ],
            ],
        ];
    }

    /**
     * Проверяет, что объект првильно определит, что данные могут быть обработаны.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideSupportsNormalization')]
    public function testSupportsNormalization(mixed $data, bool $expected): void
    {
        $normalizer = new FiasUnpackerFileNormalizer();

        $res = $normalizer->supportsNormalization($data, 'json', []);

        $this->assertSame($expected, $res);
    }

    public static function provideSupportsNormalization(): array
    {
        return [
            'file object' => [
                UnpackerFileFactory::create(
                    new \SplFileInfo('/test'),
                    'test.txt',
                    10,
                    10
                ),
                true,
            ],
            'not a file object' => [
                new \stdClass(),
                false,
            ],
            'scalar value' => [
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
        $denormalizer = new FiasUnpackerFileNormalizer();

        $res = $denormalizer->getSupportedTypes($format);

        $this->assertSame($expected, $res);
    }

    public static function provideGetSupportedTypes(): array
    {
        return [
            'xml' => [
                FiasSerializerFormat::XML->value,
                [
                    UnpackerFileImpl::class => true,
                ],
            ],
            'json' => [
                'json',
                [
                    UnpackerFileImpl::class => true,
                ],
            ],
        ];
    }
}
