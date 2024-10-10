<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\UnpackerZip;

/**
 * Тест для объекта, который распаковывает zip архив.
 *
 * @internal
 */
final class UnpackerZipTest extends BaseCase
{
    /**
     * Проверяет, что объект распакует zip архив.
     */
    public function testUnpack(): void
    {
        $testArchive = __DIR__ . '/_fixtures/testUnpack.zip';
        $testDestination = $this->getPathToTestDir('testUnpack');

        $unpacker = new UnpackerZip();
        $res = $unpacker->unpack(
            new \SplFileInfo($testArchive),
            new \SplFileInfo($testDestination)
        );

        $this->assertFileExists($testDestination . '/test.txt');
        $this->assertSame('test', file_get_contents($testDestination . '/test.txt'));
        $this->assertSame($testDestination, $res->getRealPath());
    }

    /**
     * Проверяет, что объект перехватит исключение при распаковке.
     */
    public function testUnpackOpenException(): void
    {
        $testArchive = __DIR__ . '/_fixtures/testUnpackOpenException.zip';
        $testDestination = $this->getPathToTestDir('testUnpack');

        $unpacker = new UnpackerZip();

        $this->expectException(UnpackerException::class);
        $this->expectExceptionMessage("Can't open");
        $unpacker->unpack(
            new \SplFileInfo($testArchive),
            new \SplFileInfo($testDestination)
        );
    }

    /**
     * Проверяет, что объект перехватит исключение при распаковке.
     */
    public function testUnpackException(): void
    {
        $testArchive = __DIR__ . '/_fixtures/testUnpackException.zip';
        $testDestination = '/123erwerwerwer_qweqweqweqwe';

        $unpacker = new UnpackerZip();

        $this->expectException(UnpackerException::class);
        $this->expectExceptionMessage("Can't unpack");
        $unpacker->unpack(
            new \SplFileInfo($testArchive),
            new \SplFileInfo($testDestination)
        );
    }

    /**
     * Проверяет, что объект распакует указанный файл из zip архива.
     */
    public function testUnpackFile(): void
    {
        $testArchive = __DIR__ . '/_fixtures/testUnpackFile.zip';
        $testDestination = $this->getPathToTestDir('testUnpackFile');

        $expectedPath = $testDestination . '/level2/level_2.txt';
        $expectedContent = 'level_2';

        $unpacker = new UnpackerZip();
        $res = $unpacker->unpackFile(
            new \SplFileInfo($testArchive),
            'level2/level_2.txt',
            new \SplFileInfo($testDestination)
        );

        $this->assertFileExists($expectedPath);
        $this->assertSame($expectedContent, file_get_contents($expectedPath));
        $this->assertSame($expectedPath, $res->getRealPath());
    }

    /**
     * Проверяет, что объект перехватит исключение при распаковке.
     */
    public function testUnpackFileException(): void
    {
        $testArchive = __DIR__ . '/_fixtures/testUnpackFileException.zip';
        $testDestination = $this->getPathToTestDir('testUnpackFileException');

        $unpacker = new UnpackerZip();

        $this->expectException(UnpackerException::class);
        $this->expectExceptionMessage("Can't extract entity");
        $unpacker->unpackFile(
            new \SplFileInfo($testArchive),
            'non existed entity',
            new \SplFileInfo($testDestination)
        );
    }

    /**
     * Проверяет, что объект правильно вернет список файлов из архива.
     */
    public function testGetListOfFiles(): void
    {
        $testArchive = __DIR__ . '/_fixtures/testGetListOfFiles.zip';
        $expected = [
            [
                'name' => 'level2/level_2.txt',
                'size' => 7,
            ],
            [
                'name' => 'level_1.txt',
                'size' => 7,
            ],
        ];

        $unpacker = new UnpackerZip();
        $res = [];
        foreach ($unpacker->getListOfFiles(new \SplFileInfo($testArchive)) as $file) {
            $res[] = [
                'name' => $file->getName(),
                'size' => $file->getSize(),
            ];
        }

        $this->assertSame($expected, $res);
    }

    /**
     * Проверяет, что объект правильно определит является ли файл архивом или нет.
     *
     * @dataProvider provideIsArchive
     */
    public function testIsArchive(string $path, bool $expected): void
    {
        $unpacker = new UnpackerZip();
        $res = $unpacker->isArchive(new \SplFileInfo($path));

        $this->assertSame($expected, $res);
    }

    public static function provideIsArchive(): array
    {
        return [
            'archive' => [
                __DIR__ . '/_fixtures/testIsArchive_archive.zip',
                true,
            ],
            'not archive' => [
                __DIR__ . '/_fixtures/testIsArchive_not_archive.zip',
                false,
            ],
        ];
    }
}
