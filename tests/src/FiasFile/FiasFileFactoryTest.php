<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasFile;

use Liquetsoft\Fias\Component\FiasFile\FiasFileFactory;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для фабрики, которая создает объект для фвйлов.
 *
 * @internal
 */
final class FiasFileFactoryTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно возвращает.
     */
    public function testCreate(): void
    {
        $name = 'test.txt';
        $size = 123;

        $res = FiasFileFactory::create($name, $size);

        $this->assertSame($name, $res->getName());
        $this->assertSame($size, $res->getSize());
    }

    /**
     * Проверяет, что объект правильно возвращает сущность из SplFileInfo.
     */
    public function testCreateFromZipStats(): void
    {
        $name = 'test.txt';
        $size = 123;

        $file = $this->mock(\SplFileInfo::class);
        $file->expects($this->any())->method('getPathname')->willReturn($name);
        $file->expects($this->any())->method('getSize')->willReturn($size);

        $res = FiasFileFactory::createFromSplFileInfo($file);

        $this->assertSame($name, $res->getName());
        $this->assertSame($size, $res->getSize());
    }
}
