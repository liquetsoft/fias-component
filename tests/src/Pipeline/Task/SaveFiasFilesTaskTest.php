<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\SaveFiasFilesTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;

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
        $sourceFile = $this->getPathToTestFile('SaveFiasFilesTaskTest_source.txt');
        $sourceDir = $this->getPathToTestDir('SaveFiasFilesTaskTest_source');

        $destinationFile = $this->getTempDir() . '/SaveFiasFilesTaskTest_dest.txt';
        $destinationDir = $this->getTempDir() . '/SaveFiasFilesTaskTest_dest';

        file_put_contents("{$sourceDir}/extracted_file.txt", 'test');

        $state = $this->createStateMock(
            [
                StateParameter::PATH_TO_DOWNLOAD_FILE->value => $sourceFile,
                StateParameter::PATH_TO_EXTRACT_FOLDER->value => $sourceDir,
            ]
        );

        $task = new SaveFiasFilesTask($destinationFile, $destinationDir);
        $task->run($state);

        $this->assertFileExists($destinationFile);
        $this->assertFileExists($destinationDir);
        $this->assertFileDoesNotExist($sourceFile);
        $this->assertFileDoesNotExist($sourceDir);
    }
}
