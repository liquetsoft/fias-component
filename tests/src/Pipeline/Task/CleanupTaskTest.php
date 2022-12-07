<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\CleanupTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для задачи, которая очищает все временные данные после завершения импорта.
 *
 * @internal
 */
class CleanupTaskTest extends BaseCase
{
    /**
     * Проверяет, что задача очищает все папки и файлы.
     *
     * @throws \Exception
     */
    public function testRun(): void
    {
        $downloadToPath = $this->getPathToTestFile('downloadTo.rar');
        $downloadTo = new \SplFileInfo($downloadToPath);

        $extractToDir = $this->getPathToTestDir('extractTo');
        $this->getPathToTestDir('extractTo/subDir');
        $extractToPath = $this->getPathToTestFile('extractTo/subDir/downloadTo.rar');
        $extractTo = new \SplFileInfo($extractToDir);

        $state = $this->createDefaultStateMock(
            [
                StateParameter::DOWNLOAD_TO_FILE => $downloadTo,
                StateParameter::EXTRACT_TO_FOLDER => $extractTo,
            ]
        );

        $task = new CleanupTask();
        $task->run($state);

        $this->assertFileDoesNotExist($downloadToPath, 'Downloaded file removed');
        $this->assertFileDoesNotExist($extractToPath, 'Extracted files removed');
    }

    /**
     * Проверяет, что задача очищает все папки и файлы.
     *
     * @throws \Exception
     */
    public function testRunEmptyFiles(): void
    {
        $downloadToPath = __DIR__ . '/test.rar';
        $downloadTo = new \SplFileInfo($downloadToPath);

        $state = $this->createDefaultStateMock(
            [
                StateParameter::DOWNLOAD_TO_FILE => $downloadTo,
            ]
        );

        $task = new CleanupTask();
        $task->run($state);

        $this->assertFileDoesNotExist($downloadToPath, 'Downloaded file removed');
    }
}
