<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Exception;
use Liquetsoft\Fias\Component\Pipeline\Task\SaveFiasFilesTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use SplFileInfo;

/**
 * Тест для задачи, которая сохраняет файлы ФИАС после обработки.
 */
class SaveFiasFilesTaskTest extends BaseCase
{
    /**
     * Проверяет, что задача создает все папки и передает в состояние.
     *
     * @throws Exception
     */
    public function testRun(): void
    {
        $sourceFile = $this->getPathToTestFile('SaveFiasFilesTaskTest_source.txt');
        $sourceDir = $this->getPathToTestDir('SaveFiasFilesTaskTest_source');

        $destinationFile = $this->getTempDir() . '/SaveFiasFilesTaskTest_dest.txt';
        $destinationDir = $this->getTempDir() . '/SaveFiasFilesTaskTest_dest';

        file_put_contents("{$sourceDir}/extracted_file.txt", 'test');

        $state = $this->createDefaultStateMock(
            [
                Task::DOWNLOAD_TO_FILE_PARAM => new SplFileInfo($sourceFile),
                Task::EXTRACT_TO_FOLDER_PARAM => new SplFileInfo($sourceDir),
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
