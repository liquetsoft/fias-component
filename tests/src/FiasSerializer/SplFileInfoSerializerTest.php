<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasSerializer;

use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;
use Liquetsoft\Fias\Component\FiasSerializer\SplFileInfoSerializer;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\FileSystemCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

/**
 * Тест для объекта, который преобразует FiasFileSelectorFile в массив и обратно.
 *
 * @internal
 */
class SplFileInfoSerializerTest extends BaseCase
{
    use FileSystemCase;

    /**
     * Проверяет, что объект правильно нормализует данные.
     *
     * @dataProvider provideNormalize
     */
    public function testNormalize(mixed $object, string|\Exception $awaits): void
    {
        $serializer = new SplFileInfoSerializer();

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
                new InvalidArgumentException('Object must have ' . \SplFileInfo::class . ' type'),
            ],
            'file' => [
                $this->createSplFileInfoMock('/test'),
                '/test',
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
        $serializer = new SplFileInfoSerializer();

        $this->assertSame($awaits, $serializer->supportsNormalization($object));
    }

    public function provideSupportsNormalization(): array
    {
        return [
            'scalar' => ['test', false],
            'different object type' => [$this, false],
            'correct type' => [
                $this->createSplFileInfoMock('/test'),
                true,
            ],
        ];
    }

    /**
     * Проверяет, что объект правильно нормализует данные.
     *
     * @dataProvider provideDeormalize
     */
    public function testDeormalize(mixed $data, string $type, string|\Exception $awaits): void
    {
        $serializer = new SplFileInfoSerializer();

        if ($awaits instanceof \Exception) {
            $this->expectExceptionObject($awaits);
        }

        $res = $serializer->denormalize($data, $type);

        if (!($awaits instanceof \Exception)) {
            $this->assertInstanceOf(\SplFileInfo::class, $res);
            $this->assertSame($awaits, $res->getPathName());
        }
    }

    public function provideDeormalize(): array
    {
        return [
            'non string data' => [
                [],
                \SplFileInfo::class,
                new InvalidArgumentException('Data must be a string instance'),
            ],
            'wrong type' => [
                'test',
                self::class,
                new InvalidArgumentException('Type must be ' . \SplFileInfo::class),
            ],
            'scalar type' => [
                'test',
                'test',
                new InvalidArgumentException('Type must be ' . \SplFileInfo::class),
            ],
            'correct' => [
                '/test',
                \SplFileInfo::class,
                '/test',
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
        $serializer = new SplFileInfoSerializer();

        $this->assertSame($awaits, $serializer->supportsDenormalization($data, $type));
    }

    public function provideSupportsDenormalization(): array
    {
        return [
            'scalar type' => ['test', 'test', false],
            'different object type' => ['test', self::class, false],
            'non string data' => [[], \SplFileInfo::class, false],
            'correct type' => ['test', \SplFileInfo::class, true],
            'correct type with slashes' => ['test', '\\' . \SplFileInfo::class . '\\', true],
        ];
    }
}
