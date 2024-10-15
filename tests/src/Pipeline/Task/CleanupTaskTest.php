<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\CleanupTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Marvin255\FileSystemHelper\FileSystemHelper;

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
        $downloadToPath = '/downloadTo.zip';
        $extractToPath = '/extractTo';

        $fs = $this->mock(FileSystemHelper::class);
        $fs->expects($this->exactly(2))
            ->method('removeIfExists')
            ->with(
                $this->callback(
                    fn (string $f): bool => $f === $downloadToPath
                        || $f === $extractToPath
                )
            );

        $state = $this->createStateMock(
            [
                StateParameter::PATH_TO_DOWNLOAD_FILE->value => $downloadToPath,
                StateParameter::PATH_TO_EXTRACT_FOLDER->value => $extractToPath,
            ]
        );

        $task = new CleanupTask($fs);
        $task->run($state);
    }
}
