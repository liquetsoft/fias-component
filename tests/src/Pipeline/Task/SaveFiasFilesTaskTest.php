<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\State;
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
     */
    public function testRun()
    {
        $sourceFile = $this->getPathToTestFile('SaveFiasFilesTaskTest_source.txt');
        $sourceDir = $this->getPathToTestDir('SaveFiasFilesTaskTest_source');

        $destinationFile = $this->getTempDir() . '/SaveFiasFilesTaskTest_dest.txt';
        $destinationDir = $this->getTempDir() . '/SaveFiasFilesTaskTest_dest';

        file_put_contents("{$sourceDir}/extracted_file.txt", 'test');

        $sources = [
            Task::DOWNLOAD_TO_FILE_PARAM => $sourceFile,
            Task::EXTRACT_TO_FOLDER_PARAM => $sourceDir,
        ];

        $state = $this->getMockBuilder(State::class)->getMock();
        $state->method('getParameter')
            ->will(
                $this->returnCallback(
                    function ($paramName) use ($sources) {
                        return $sources[$paramName] ? new SplFileInfo($sources[$paramName]) : null;
                    }
                )
            );

        $task = new SaveFiasFilesTask($destinationFile, $destinationDir);
        $task->run($state);

        $this->assertFileExists($destinationFile);
        $this->assertFileExists($destinationDir);
        $this->assertFileDoesNotExist($sourceFile);
        $this->assertFileDoesNotExist($sourceDir);
    }
}
