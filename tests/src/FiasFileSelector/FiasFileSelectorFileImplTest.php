<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasFileSelector;

use Liquetsoft\Fias\Component\Exception\FiasFileSelectorException;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFileImpl;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который представляет файл для внутренней обработки.
 *
 * @internal
 */
class FiasFileSelectorFileImplTest extends BaseCase
{
    /**
     * Проверяет, что объект вернет путь до файла.
     *
     * @dataProvider provideGetPath
     */
    public function testGetPath(string $path, string|\Exception $expected): void
    {
        $size = 10;

        if ($expected instanceof \Exception) {
            $this->expectExceptionObject($expected);
        }

        $file = new FiasFileSelectorFileImpl($path, $size);
        $returnedPath = $file->getPath();

        if (!($expected instanceof \Exception)) {
            $this->assertSame($expected, $returnedPath);
        }
    }

    public function provideGetPath(): array
    {
        return [
            'empty string path' => [
                '',
                FiasFileSelectorException::create("path param can't be empty"),
            ],
            'string with spaces' => [
                '     ',
                FiasFileSelectorException::create("path param can't be empty"),
            ],
            'correct path' => [
                '/test/path.xml',
                '/test/path.xml',
            ],
            'correct path with spaces' => [
                '    /test/path.xml    ',
                '/test/path.xml',
            ],
        ];
    }

    /**
     * Проверяет, что объект вернет размер файла.
     *
     * @dataProvider provideGetSize
     */
    public function testGetSize(int $size, int|\Exception $expected): void
    {
        $path = '/test/file.xml';

        if ($expected instanceof \Exception) {
            $this->expectExceptionObject($expected);
        }

        $file = new FiasFileSelectorFileImpl($path, $size);
        $returnedSize = $file->getSize();

        if (!($expected instanceof \Exception)) {
            $this->assertSame($expected, $returnedSize);
        }
    }

    public function provideGetSize(): array
    {
        return [
            'positive size' => [10, 10],
            'zero size' => [0, 0],
            'negative size' => [-1, FiasFileSelectorException::create("Size can't be less than 0")],
        ];
    }

    /**
     * Проверяет, что объект вернет путь до архива.
     *
     * @dataProvider provideGetPathToArchive
     */
    public function testGetPathToArchive(string $archive, string|\Exception $expected): void
    {
        $path = '/test/file.xml';
        $size = 10;

        if ($expected instanceof \Exception) {
            $this->expectExceptionObject($expected);
        }

        $file = new FiasFileSelectorFileImpl($path, $size, $archive);
        $returnedPath = $file->getPathToArchive();

        if (!($expected instanceof \Exception)) {
            $this->assertSame($expected, $returnedPath);
        }
    }

    public function provideGetPathToArchive(): array
    {
        return [
            'empty string path' => [
                '',
                FiasFileSelectorException::create("pathToArchive param can't be empty"),
            ],
            'string with spaces' => [
                '     ',
                FiasFileSelectorException::create("pathToArchive param can't be empty"),
            ],
            'correct path' => [
                '/test/path.zip',
                '/test/path.zip',
            ],
            'correct path with spaces' => [
                '    /test/path.zip    ',
                '/test/path.zip',
            ],
        ];
    }

    /**
     * Проверяет, что объект выбросит исключение, если путь к архиву не указан.
     */
    public function testGetPathToArchiveException(): void
    {
        $path = '/test/file.xml';
        $size = 10;

        $file = new FiasFileSelectorFileImpl($path, $size);

        $this->expectException(FiasFileSelectorException::class);
        $file->getPathToArchive();
    }

    /**
     * Проверяет, что объект вернет правду, если файл в архиве.
     */
    public function testIsArchived(): void
    {
        $path = '/test/file.xml';
        $size = 10;
        $archive = '/test/file.zip';

        $file = new FiasFileSelectorFileImpl($path, $size, $archive);

        $this->assertTrue($file->isArchived());
    }

    /**
     * Проверяет, что объект вернет ложь, если файл не в архиве.
     */
    public function testIsNotArchived(): void
    {
        $path = '/test/file.xml';
        $size = 10;

        $file = new FiasFileSelectorFileImpl($path, $size);

        $this->assertFalse($file->isArchived());
    }
}
