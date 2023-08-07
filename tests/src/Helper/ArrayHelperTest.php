<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Helper;

use Liquetsoft\Fias\Component\Exception\Exception;
use Liquetsoft\Fias\Component\Helper\ArrayHelper;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для класса, который содержит методы для работы с массивами.
 *
 * @internal
 */
class ArrayHelperTest extends BaseCase
{
    /**
     * Проверяет, что метод верно извлечет строку из массива по имени.
     *
     * @dataProvider provideExtractStringFromArrayByName
     */
    public function testExtractStringFromArrayByName(string $name, array $array, string $awaits, string $default = null): void
    {
        if ($default === null) {
            $res = ArrayHelper::extractStringFromArrayByName($name, $array);
        } else {
            $res = ArrayHelper::extractStringFromArrayByName($name, $array, $default);
        }

        $this->assertSame($awaits, $res);
    }

    public function provideExtractStringFromArrayByName(): array
    {
        return [
            'string' => [
                'test_name',
                ['test_name' => 'test_value'],
                'test_value',
            ],
            'int' => [
                'test_name',
                ['test_name' => 123],
                '123',
            ],
            'default' => [
                'test_name',
                [],
                'test_value',
                'test_value',
            ],
            'default default' => [
                'test_name',
                [],
                '',
            ],
        ];
    }

    /**
     * Проверяет, что метод верно извлечет число из массива по имени.
     *
     * @dataProvider provideExtractIntFromArrayByName
     */
    public function testExtractIntFromArrayByName(string $name, array $array, int $awaits, int $default = null): void
    {
        if ($default === null) {
            $res = ArrayHelper::extractIntFromArrayByName($name, $array);
        } else {
            $res = ArrayHelper::extractIntFromArrayByName($name, $array, $default);
        }

        $this->assertSame($awaits, $res);
    }

    public function provideExtractIntFromArrayByName(): array
    {
        return [
            'string' => [
                'test_name',
                ['test_name' => '123'],
                123,
            ],
            'int' => [
                'test_name',
                ['test_name' => 123],
                123,
            ],
            'default' => [
                'test_name',
                [],
                123,
                123,
            ],
            'default default' => [
                'test_name',
                [],
                0,
            ],
        ];
    }

    /**
     * Проверяет, что метод верно извлечет число из массива по имени.
     *
     * @dataProvider provideExtractBoolFromArrayByName
     */
    public function testExtractBoolFromArrayByName(string $name, array $array, bool $awaits, bool $default = null): void
    {
        if ($default === null) {
            $res = ArrayHelper::extractBoolFromArrayByName($name, $array);
        } else {
            $res = ArrayHelper::extractBoolFromArrayByName($name, $array, $default);
        }

        $this->assertSame($awaits, $res);
    }

    public function provideExtractBoolFromArrayByName(): array
    {
        return [
            'string' => [
                'test_name',
                ['test_name' => '1'],
                true,
            ],
            'bool' => [
                'test_name',
                ['test_name' => false],
                false,
            ],
            'default' => [
                'test_name',
                [],
                true,
                true,
            ],
            'default default' => [
                'test_name',
                [],
                false,
            ],
        ];
    }

    /**
     * Проверяет, что метод верно извлечет массив из массива по имени.
     *
     * @dataProvider provideExtractArrayFromArrayByName
     */
    public function testExtractArrayFromArrayByName(string $name, array $array, array $awaits, array $default = null): void
    {
        if ($default === null) {
            $res = ArrayHelper::extractArrayFromArrayByName($name, $array);
        } else {
            $res = ArrayHelper::extractArrayFromArrayByName($name, $array, $default);
        }

        $this->assertSame($awaits, $res);
    }

    public function provideExtractArrayFromArrayByName(): array
    {
        return [
            'array' => [
                'test_name',
                ['test_name' => [1, 2]],
                [1, 2],
            ],
            'default' => [
                'test_name',
                [],
                [1, 2],
                [1, 2],
            ],
            'default default' => [
                'test_name',
                [],
                [],
            ],
            'object' => [
                'test_name',
                ['test_name' => new \stdClass()],
                [],
            ],
        ];
    }

    /**
     * Проверяет, что метод проверит типы всех элементов массива.
     *
     * @psalm-param class-string $type
     *
     * @dataProvider provideEnsureArrayElements
     */
    public function testEnsureArrayElements(mixed $array, string $type, array|\Exception $awaits): void
    {
        if ($awaits instanceof \Exception) {
            $this->expectExceptionObject($awaits);
        }

        $res = ArrayHelper::ensureArrayElements($array, $type);

        if (!($awaits instanceof \Exception)) {
            $this->assertSame($awaits, $res);
        }
    }

    public function provideEnsureArrayElements(): array
    {
        return [
            'empty array' => [
                [],
                self::class,
                [],
            ],
            'array with correct items' => [
                [$this, $this],
                self::class,
                [$this, $this],
            ],
            'array with correct child types' => [
                [$this, $this],
                BaseCase::class,
                [$this, $this],
            ],
            'not an array' => [
                'test',
                self::class,
                Exception::create('Data must be an instance of array'),
            ],
            'array with incorrect itemy' => [
                [$this, 'test'],
                self::class,
                Exception::create('All items must be instances of %s', self::class),
            ],
        ];
    }
}
