<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasSerializer;

use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;
use Liquetsoft\Fias\Component\FiasSerializer\FiasFileSelectorFileSerializer;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\FiasFileSelectorCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

/**
 * Тест для объекта, который преобразует FiasFileSelectorFile в массив и обратно.
 *
 * @internal
 */
class FiasFileSelectorFileSerializerTest extends BaseCase
{
    use FiasFileSelectorCase;

    /**
     * Проверяет, что объект правильно нормализует данные.
     *
     * @dataProvider provideNormalize
     */
    public function testNormalize(mixed $object, array|\Exception $awaits): void
    {
        $serializer = new FiasFileSelectorFileSerializer();

        if ($awaits instanceof \Exception) {
            $this->expectExceptionObject($awaits);
        }

        $res = $serializer->normalize($object);

        if (!($awaits instanceof \Exception)) {
            $this->assertSame($awaits, $res);
        }
    }

    public function provideNormalize(): array
    {
        return [
            'scalar target' => [
                'test',
                new InvalidArgumentException('Object must have ' . FiasFileSelectorFile::class . ' type'),
            ],
            'file' => [
                $this->createFiasFileSelectorFileMock('/path', 123),
                [
                    'path' => '/path',
                    'size' => 123,
                    'pathToArchive' => null,
                ],
            ],
            'archive' => [
                $this->createFiasFileSelectorFileMock('/path', 123, '/archive'),
                [
                    'path' => '/path',
                    'size' => 123,
                    'pathToArchive' => '/archive',
                ],
            ],
        ];
    }

    /**
     * Проверяет, что объект правильно определит цель для нормализации.
     *
     * @dataProvider provideSupportsNormalization
     */
    public function testSupportsNormalization(mixed $object, bool $awaits): void
    {
        $serializer = new FiasFileSelectorFileSerializer();

        $this->assertSame($awaits, $serializer->supportsNormalization($object));
    }

    public function provideSupportsNormalization(): array
    {
        return [
            'scalar' => ['test', false],
            'different object type' => [$this, false],
            'correct type' => [
                $this->createFiasFileSelectorFileMock(),
                true,
            ],
        ];
    }

    /**
     * Проверяет, что объект правильно нормализует данные.
     *
     * @dataProvider provideDeormalize
     */
    public function testDeormalize(mixed $data, string $type, array|\Exception $awaits): void
    {
        $serializer = new FiasFileSelectorFileSerializer();

        if ($awaits instanceof \Exception) {
            $this->expectExceptionObject($awaits);
        }

        $res = $serializer->denormalize($data, $type);

        if (!($awaits instanceof \Exception)) {
            $this->assertInstanceOf(FiasFileSelectorFile::class, $res);
            $this->assertSame(
                $awaits,
                [
                    'path' => $res->getPath(),
                    'size' => $res->getSize(),
                    'pathToArchive' => $res->isArchived() ? $res->getPathToArchive() : null,
                ]
            );
        }
    }

    public function provideDeormalize(): array
    {
        return [
            'not array data' => [
                'test',
                FiasFileSelectorFile::class,
                new InvalidArgumentException('Data must be an array instance'),
            ],
            'wrong type' => [
                [],
                self::class,
                new InvalidArgumentException('Type must be ' . FiasFileSelectorFile::class),
            ],
            'scalar type' => [
                [],
                'test',
                new InvalidArgumentException('Type must be ' . FiasFileSelectorFile::class),
            ],
            'correct' => [
                ['path' => '/path', 'size' => 123, 'pathToArchive' => '/archive', 'test' => 'test'],
                FiasFileSelectorFile::class,
                ['path' => '/path', 'size' => 123, 'pathToArchive' => '/archive'],
            ],
        ];
    }

    /**
     * Проверяет, что объект правильно определит цель для денормализации.
     *
     * @dataProvider provideSupportsDenormalization
     */
    public function testSupportsDenormalization(mixed $data, string $type, bool $awaits): void
    {
        $serializer = new FiasFileSelectorFileSerializer();

        $this->assertSame($awaits, $serializer->supportsDenormalization($data, $type));
    }

    public function provideSupportsDenormalization(): array
    {
        return [
            'scalar' => [[], 'test', false],
            'different object type' => [[], self::class, false],
            'correct type' => [[], FiasFileSelectorFile::class, true],
            'correct type with slashes' => [[], '\\' . FiasFileSelectorFile::class . '\\', true],
        ];
    }
}
