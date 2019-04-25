<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Unpacker;

use Liquetsoft\Fias\Component\Unpacker\RarUnpacker;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Exception\UnpackerException;
use SplFileInfo;
use InvalidArgumentException;

/**
 * Тест для объекта, который распаковывает данные из rar архива.
 */
class RarUnpackerTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если файла с архивом не
     * существует.
     */
    public function testUnpackUnexistedSourceException()
    {
        $source = new SplFileInfo(__DIR__ . '/empty.rar');
        $destination = new SplFileInfo($this->getPathToTestDir());

        $unpacker = new RarUnpacker;

        $this->expectException(InvalidArgumentException::class);
        $unpacker->unpack($source, $destination);
    }

    /**
     * Проверяет, что объект выбросит исключение, если папки, в которую должен
     * быть распакован архив, не существует.
     */
    public function testUnpackUnexistedDestinationException()
    {
        $source = new SplFileInfo($this->getPathToTestFile('testUnpackUnexistedDestinationException.rar'));
        $destination = new SplFileInfo('/unexisted/destination');

        $unpacker = new RarUnpacker;

        $this->expectException(InvalidArgumentException::class);
        $unpacker->unpack($source, $destination);
    }

    /**
     * Проверяет распаковку архива.
     */
    public function testUnpack()
    {
        $sourcePath = __DIR__ . '/_fixtures/testUnpack.rar';
        $source = new SplFileInfo($sourcePath);

        $destinationPath = $this->getPathToTestDir('testUnpack');
        $destination = new SplFileInfo($destinationPath);

        $unpacker = new RarUnpacker;
        $unpacker->unpack($source, $destination);

        $this->assertFileExists($destinationPath . '/test.txt');
        $this->assertSame('test.txt', file_get_contents($destinationPath . '/test.txt'));
        $this->assertFileExists($destinationPath . '/test_2.txt');
        $this->assertSame('test_2.txt', file_get_contents($destinationPath . '/test_2.txt'));
    }

    /**
     * Проверяет , что объект верно обработает битый архив.
     */
    public function testUnpackBrokenArchiveException()
    {
        $unpacker = new RarUnpacker;

        $source = new SplFileInfo($this->getPathToTestFile('broken.rar'));
        $destination = new SplFileInfo($this->getPathToTestDir('testUnpack'));

        $this->expectException(UnpackerException::class);
        $unpacker->unpack($source, $destination);
    }
}
