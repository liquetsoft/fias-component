<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Unpacker;

use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFileImpl;

/**
 * Тест для объекта, который представляет файл внутри архива.
 *
 * @internal
 */
final class UnpackerFileImplTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно возвращает путь до архива.
     */
    public function testGetArchive(): void
    {
        $expected = $this->mock(\SplFileInfo::class);

        $file = $this->createUnpackerFileImpl(archiveFile: $expected);
        $res = $file->getArchiveFile();

        $this->assertSame($expected, $res);
    }

    /**
     * Проверяет, что объект правильно возвращает имя файла.
     */
    public function testGetName(): void
    {
        $expected = 'test.txt';

        $file = $this->createUnpackerFileImpl(name: $expected);
        $res = $file->getName();

        $this->assertSame($expected, $res);
    }

    /**
     * Проверяет, что объект правильно возвращает индекс файла.
     */
    public function testGetIndex(): void
    {
        $expected = 123;

        $file = $this->createUnpackerFileImpl(index: $expected);
        $res = $file->getIndex();

        $this->assertSame($expected, $res);
    }

    /**
     * Проверяет, что объект правильно возвращает размер файла.
     */
    public function testGetSize(): void
    {
        $expected = 123;

        $file = $this->createUnpackerFileImpl(size: $expected);
        $res = $file->getSize();

        $this->assertSame($expected, $res);
    }

    private function createUnpackerFileImpl(?\SplFileInfo $archiveFile = null, string $name = '', int $index = 0, int $size = 0): UnpackerFileImpl
    {
        $archiveFile = $archiveFile ?? new \SplFileInfo('test');

        return new UnpackerFileImpl($archiveFile, $name, $index, $size);
    }
}
