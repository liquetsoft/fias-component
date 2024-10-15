<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\SaveFiasFilesTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Marvin255\FileSystemHelper\FileSystemHelper;

/**
 * Тест для задачи, которая сохраняет файлы ФИАС после обработки.
 *
 * @internal
 */
final class SaveFiasFilesTaskTest extends BaseCase
{
    /**
     * Проверяет, что задача создает все папки и передает в состояние.
     */
    public function testRun(): void
    {
        $moveArchiveTo = 'moveArchiveTo.zip';
        $moveExtractedTo = '/moveExtractedTo';

        $sourceFile = 'source.zip';
        $sourceDir = '/source';

        $fs = $this->mock(FileSystemHelper::class);
        $fs->expects($this->exactly(2))
            ->method('rename')
            ->with(
                $this->callback(
                    fn (string $f): bool => $f === $sourceFile || $f === $sourceDir
                ),
                $this->callback(
                    fn (string $f): bool => $f === $moveExtractedTo || $f === $moveArchiveTo
                )
            );

        $state = $this->createStateMock(
            [
                StateParameter::PATH_TO_DOWNLOAD_FILE->value => $sourceFile,
                StateParameter::PATH_TO_EXTRACT_FOLDER->value => $sourceDir,
            ]
        );

        $task = new SaveFiasFilesTask($moveExtractedTo, $moveArchiveTo, $fs);
        $task->run($state);
    }
}
