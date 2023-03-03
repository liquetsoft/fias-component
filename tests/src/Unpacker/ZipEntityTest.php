<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\ZipEntity;

/**
 * Тест для объекта, который представляет файл в архиве.
 *
 * @internal
 */
class ZipEntityTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если задан неверный параметр конструктора.
     */
    public function testConstructException(): void
    {
        $this->expectException(UnpackerException::class);
        new ZipEntity(false);
    }

    /**
     * Проверяет, что объект вернет правильный тип, если сущность - файл.
     *
     * @dataProvider provideIsFile
     */
    public function testIsFile(array $stats, bool $awaits): void
    {
        $entity = new ZipEntity($stats);
        $isFile = $entity->isFile();

        $this->assertSame($awaits, $isFile);
    }

    public function provideIsFile(): array
    {
        return [
            'file' => [['crc' => 10], true],
            'string param' => [['crc' => '10'], true],
            'not file' => [['crc' => 0], false],
            'no param' => [[], false],
        ];
    }

    /**
     * Проверяет, что объект вернет правильный индекс.
     *
     * @dataProvider provideGetIndex
     */
    public function testGetIndex(array $stats, int $awaits): void
    {
        $entity = new ZipEntity($stats);
        $index = $entity->getIndex();

        $this->assertSame($awaits, $index);
    }

    public function provideGetIndex(): array
    {
        return [
            'index' => [['index' => 123], 123],
            'index string' => [['index' => '123'], 123],
            'no param' => [[], 0],
        ];
    }

    /**
     * Проверяет, что объект вернет правильный размер файла.
     *
     * @dataProvider provideGetSize
     */
    public function testGetSize(array $stats, int $size): void
    {
        $entity = new ZipEntity($stats);
        $index = $entity->getSize();

        $this->assertSame($size, $index);
    }

    public function provideGetSize(): array
    {
        return [
            'size' => [['size' => 123], 123],
            'size string' => [['size' => '123'], 123],
            'no param' => [[], 0],
        ];
    }

    /**
     * Проверяет, что объект вернет правильное имя файла.
     *
     * @dataProvider provideGetName
     */
    public function testGetName(array $stats, string $awaits): void
    {
        $entity = new ZipEntity($stats);
        $name = $entity->getName();

        $this->assertSame($awaits, $name);
    }

    public function provideGetName(): array
    {
        return [
            'name' => [['name' => 'name'], 'name'],
            'name int' => [['name' => 123], '123'],
            'no param' => [[], ''],
        ];
    }
}
