<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Serializer;

use Liquetsoft\Fias\Component\Serializer\FiasSerializerFormat;
use Liquetsoft\Fias\Component\Serializer\FiasUnpackerFileDenormalizer;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFile;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFileImpl;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

/**
 * Тест для объекта, который преобразует массив в объект файла из архива.
 *
 * @internal
 */
final class FiasUnpackerFileDenormalizerTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно денормализует данные.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideDenormalize')]
    public function testDenormalize(mixed $data, array|\Exception $expected): void
    {
        $denormalizer = new FiasUnpackerFileDenormalizer();

        if ($expected instanceof \Exception) {
            $this->expectExceptionObject($expected);
        }

        $res = $denormalizer->denormalize($data, UnpackerFile::class);

        if (!($expected instanceof \Exception)) {
            $this->assertInstanceOf(UnpackerFile::class, $res);
            $this->assertSame(
                $expected,
                [
                    'archiveFile' => $res->getArchiveFile()->getPathname(),
                    'name' => $res->getName(),
                    'index' => $res->getIndex(),
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
                    'archiveFile' => '/test.zip',
                    'name' => 'test.txt',
                    'index' => 10,
                    'size' => 213,
                ],
                [
                    'archiveFile' => '/test.zip',
                    'name' => 'test.txt',
                    'index' => 10,
                    'size' => 213,
                ],
            ],
            'no archiveFile' => [
                [
                    'name' => 'test.txt',
                    'index' => 10,
                    'size' => 213,
                ],
                new InvalidArgumentException("'archiveFile' param isn't set"),
            ],
            'no name' => [
                [
                    'archiveFile' => '/test.zip',
                    'index' => 10,
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
        $denormalizer = new FiasUnpackerFileDenormalizer();

        $res = $denormalizer->supportsDenormalization([], $type, 'json', []);

        $this->assertSame($expected, $res);
    }

    public static function provideSupportsDenormalization(): array
    {
        return [
            'implementation' => [
                UnpackerFileImpl::class,
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
        $denormalizer = new FiasUnpackerFileDenormalizer();

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
