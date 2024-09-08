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
final class CleanupTaskTest extends BaseCase
{
    /**
     * Проверяет, что задача очищает все папки и файлы.
     */
    public function testRun(): void
    {
        $downloadToPath = $this->getPathToTestFile('downloadTo.rar');
        $extractToPath = $this->getPathToTestDir('extractTo');

        $state = $this->createDefaultStateMock(
            [
                StateParameter::PATH_TO_DOWNLOAD_FILE->value => $downloadToPath,
                StateParameter::PATH_TO_EXTRACT_FOLDER->value => $extractToPath,
            ]
        );

        $task = new CleanupTask();
        $task->run($state);

        $this->assertFileDoesNotExist($downloadToPath, 'Downloaded file removed');
        $this->assertFileDoesNotExist($extractToPath, 'Extracted files removed');
    }
}
