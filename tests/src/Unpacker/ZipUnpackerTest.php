<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\ZipEntity;
use Liquetsoft\Fias\Component\Unpacker\ZipUnpacker;

/**
 * Тест для объекта, который распаковывает zip архив.
 *
 * @internal
 */
class ZipUnpackerTest extends BaseCase
{
    /**
     * Проверяет, что объект распакует zip архив.
     *
     * @throws UnpackerException
     */
    public function testUnpack(): void
    {
        $testArchive = __DIR__ . '/_fixtures/testUnpack.zip';
        $testDestination = $this->getPathToTestDir('testUnpack');

        $zipUnpack = new ZipUnpacker();
        $zipUnpack->unpack(
            new \SplFileInfo($testArchive),
            new \SplFileInfo($testDestination)
        );

        $this->assertFileExists($testDestination . '/test.txt');
        $this->assertSame('test', trim(file_get_contents($testDestination . '/test.txt')));
        $this->assertFileExists($testDestination . '/nested/nested_file.txt');
        $this->assertSame('nested_file', trim(file_get_contents($testDestination . '/nested/nested_file.txt')));
    }

    /**
     * Проверяет, что объект перехватит исключение при распаковке.
     */
    public function testUnpackException(): void
    {
        $testArchive = __DIR__ . '/_fixtures/testUnpackException.zip';
        $testDestination = $this->getPathToTestDir('testUnpack');

        $zipUnpack = new ZipUnpacker();

        $this->expectException(UnpackerException::class);
        $zipUnpack->unpack(
            new \SplFileInfo($testArchive),
            new \SplFileInfo($testDestination)
        );
    }

    /**
     * Проверяет, что объект вернет список файлов в архиве.
     */
    public function testGetListOfFiles(): void
    {
        $testArchive = __DIR__ . '/_fixtures/testUnpack.zip';

        $zipUnpack = new ZipUnpacker();
        $files = $zipUnpack->getListOfFiles(new \SplFileInfo($testArchive));

        $this->assertCount(2, $files);
        $this->assertArrayHasKey(0, $files);
        $this->assertInstanceOf(ZipEntity::class, $files[0]);
        $this->assertSame('nested/nested_file.txt', $files[0]->getName());
        $this->assertArrayHasKey(1, $files);
        $this->assertInstanceOf(ZipEntity::class, $files[1]);
        $this->assertSame('test.txt', $files[1]->getName());
    }

    /**
     * Проверяет, что объект перехватит исключение при возвращении списка.
     */
    public function testGetListOfFilesException(): void
    {
        $testArchive = __DIR__ . '/_fixtures/testUnpackException.zip';

        $zipUnpack = new ZipUnpacker();

        $this->expectException(UnpackerException::class);
        $zipUnpack->getListOfFiles(new \SplFileInfo($testArchive));
    }

    /**
     * Проверяет, что объект сможет извлечь единичный файл.
     */
    public function testExtractEntityByFileName(): void
    {
        $testArchive = __DIR__ . '/_fixtures/testUnpack.zip';
        $testDestination = $this->getPathToTestDir('testExtractEntity');

        $zipUnpack = new ZipUnpacker();
        $path = $zipUnpack->extractEntity(
            new \SplFileInfo($testArchive),
            'nested/nested_file.txt',
            new \SplFileInfo($testDestination)
        );

        $this->assertStringStartsWith($testDestination, $path);
        $this->assertFileExists($path);
        $this->assertSame('nested_file', trim(file_get_contents($path)));
    }

    /**
     * Проверяет, что объект выбросит исключение при попытке распаковать несуществующий файл.
     */
    public function testExtractEntityNonExistedException(): void
    {
        $testArchive = __DIR__ . '/_fixtures/testUnpack.zip';
        $testDestination = $this->getPathToTestDir('testExtractEntity');

        $zipUnpack = new ZipUnpacker();

        $this->expectException(UnpackerException::class);
        $zipUnpack->extractEntity(
            new \SplFileInfo($testArchive),
            'non_existed',
            new \SplFileInfo($testDestination)
        );
    }
}
