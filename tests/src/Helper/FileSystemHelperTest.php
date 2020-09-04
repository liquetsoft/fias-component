<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Helper;

use Liquetsoft\Fias\Component\Helper\FileSystemHelper;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use SplFileInfo;

/**
 * Тест для хэлпера, который содержит операции с файловой системой.
 */
class FileSystemHelperTest extends BaseCase
{
    /**
     * Тест, который проверяет, что хэлпер удалит файл.
     */
    public function testRemoveFile()
    {
        $sourceFile = $this->getPathToTestFile('remove_file_test.txt', 'test');

        FileSystemHelper::remove(new SplFileInfo($sourceFile));

        $this->assertFileDoesNotExist($sourceFile);
    }

    /**
     * Тест, который проверяет, что хэлпер рекурсивно удалит каталог.
     */
    public function testRemoveDir()
    {
        $sourceDir = $this->getPathToTestDir('remove_dir_test');
        $sourceFile = "{$sourceDir}/remove_dir_test.txt";

        file_put_contents($sourceFile, 'remove_dir_test');

        FileSystemHelper::remove(new SplFileInfo($sourceDir));

        $this->assertFileDoesNotExist($sourceDir);
        $this->assertFileDoesNotExist($sourceFile);
    }

    /**
     * Тест, который проверяет, что хэлпер перенесет файл.
     */
    public function testMoveFile()
    {
        $sourceFile = $this->getPathToTestFile('move_file_source.txt');

        $destinationDir = $this->getPathToTestDir('move_file_destination');
        $destinationFile = "{$destinationDir}/move_destination.txt";

        FileSystemHelper::move(
            new SplFileInfo($sourceFile),
            new SplFileInfo($destinationFile)
        );

        $this->assertFileDoesNotExist($sourceFile);
        $this->assertFileExists($destinationFile);
    }

    /**
     * Тест, который проверяет, что хэлпер перенесет директорию.
     */
    public function testMoveDir()
    {
        $sourceDir = $this->getPathToTestDir('move_dir_source');
        $sourceFile = "{$sourceDir}/move_dir_source.txt";

        $destinationDir = $this->getTempDir() . '/move_dir_destination';
        $destinationFile = "{$destinationDir}/move_dir_source.txt";

        file_put_contents($sourceFile, 'move_dir_source');

        FileSystemHelper::move(
            new SplFileInfo($sourceDir),
            new SplFileInfo($destinationDir)
        );

        $this->assertFileDoesNotExist($sourceDir);
        $this->assertFileExists($destinationDir);
        $this->assertFileExists($destinationFile);
    }
}
