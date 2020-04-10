<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\ZipUnpacker;
use SplFileInfo;

/**
 * Тест для объекта, который распаковывает zip архив.
 */
class ZipUnpackerTest extends BaseCase
{
    /**
     * Проверяет, что объект распакует zip архив.
     */
    public function testUnpack()
    {
        $testArchive = __DIR__ . '/_fixtures/testUnpack.zip';
        $testDestination = $this->getPathToTestDir('testUnpack');

        $zipUnpacker = new ZipUnpacker();
        $zipUnpacker->unpack(
            new SplFileInfo($testArchive),
            new SplFileInfo($testDestination)
        );

        $this->assertFileExists($testDestination . '/test.txt');
        $this->assertEquals('test', trim(file_get_contents($testDestination . '/test.txt')));
    }

    /**
     * Проверяет, что объект перехватит исключение при распаковке.
     */
    public function testUnpackException()
    {
        $testArchive = __DIR__ . '/_fixtures/testUnpack.rar';
        $testDestination = $this->getPathToTestDir('testUnpack');

        $this->expectException(UnpackerException::class);

        $zipUnpacker = new ZipUnpacker();
        $zipUnpacker->unpack(
            new SplFileInfo($testArchive),
            new SplFileInfo($testDestination)
        );
    }
}
