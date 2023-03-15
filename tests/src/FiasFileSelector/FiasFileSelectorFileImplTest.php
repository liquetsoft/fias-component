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
     */
    public function testGetPath(): void
    {
        $path = '/test/file.xml';
        $size = 10;

        $file = new FiasFileSelectorFileImpl($path, $size);
        $returnedPath = $file->getPath();

        $this->assertSame($path, $returnedPath);
    }

    /**
     * Проверяет, что объект вернет размер файла.
     */
    public function testGetSize(): void
    {
        $path = '/test/file.xml';
        $size = 10;

        $file = new FiasFileSelectorFileImpl($path, $size);
        $returnedSize = $file->getSize();

        $this->assertSame($size, $returnedSize);
    }

    /**
     * Проверяет, что объект вернет путь до архива.
     */
    public function testGetPathToArchive(): void
    {
        $path = '/test/file.xml';
        $size = 10;
        $archive = '/test/file.zip';

        $file = new FiasFileSelectorFileImpl($path, $size, $archive);
        $returnedArchive = $file->getPathToArchive();

        $this->assertSame($archive, $returnedArchive);
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
