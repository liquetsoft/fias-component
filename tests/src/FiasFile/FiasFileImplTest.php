<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasFile;

use Liquetsoft\Fias\Component\FiasFile\FiasFileImpl;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который представляет файл.
 *
 * @internal
 */
final class FiasFileImplTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно возвращает имя файла.
     */
    public function testGetName(): void
    {
        $expected = 'test.txt';

        $file = $this->createFileImpl(name: $expected);
        $res = $file->getName();

        $this->assertSame($expected, $res);
    }

    /**
     * Проверяет, что объект правильно возвращает размер файла.
     */
    public function testGetSize(): void
    {
        $expected = 123;

        $file = $this->createFileImpl(size: $expected);
        $res = $file->getSize();

        $this->assertSame($expected, $res);
    }

    /**
     * Проверяет, что объект возвращает имя файла ри преобразовании в строку.
     */
    public function testToString(): void
    {
        $expected = 'test.txt';

        $file = $this->createFileImpl(name: $expected);
        $res = (string) $file;

        $this->assertSame($expected, $res);
    }

    private function createFileImpl(string $name = '', int $size = 0): FiasFileImpl
    {
        return new FiasFileImpl($name, $size);
    }
}
