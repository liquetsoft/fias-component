<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Unpacker;

use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFileFactory;

/**
 * Тест для фабрики, которая создает объект для фвйлов в архиве.
 *
 * @internal
 */
final class UnpackerFileFactoryTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно возвращает.
     */
    public function testCreate(): void
    {
        $archive = $this->mock(\SplFileInfo::class);
        $name = 'test.txt';
        $size = 123;
        $index = 2;

        $res = UnpackerFileFactory::create($archive, $name, $index, $size);

        $this->assertSame($archive, $res->getArchiveFile());
        $this->assertSame($name, $res->getName());
        $this->assertSame($size, $res->getSize());
        $this->assertSame($index, $res->getIndex());
    }

    /**
     * Проверяет, что объект правильно возвращает сущность из описания для zip архива.
     */
    public function testCreateFromZipStats(): void
    {
        $archive = $this->mock(\SplFileInfo::class);
        $stats = [
            'name' => 'test.txt',
            'size' => 123,
            'index' => 321,
        ];

        $res = UnpackerFileFactory::createFromZipStats($archive, $stats);

        $this->assertSame($archive, $res->getArchiveFile());
        $this->assertSame($stats['name'], $res->getName());
        $this->assertSame($stats['size'], $res->getSize());
        $this->assertSame($stats['index'], $res->getIndex());
    }
}
