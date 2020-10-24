<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\Task\CleanupTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use SplFileInfo;

/**
 * Тест для задачи, которая очищает все временные данные после завершения импорта.
 */
class CleanupTaskTest extends BaseCase
{
    /**
     * Проверяет, что задача очищает все папки и файлы.
     */
    public function testRun()
    {
        $downloadToPath = $this->getPathToTestFile('downloadTo.rar');
        $downloadTo = new SplFileInfo($downloadToPath);

        $extractToDir = $this->getPathToTestDir('extractTo');
        $this->getPathToTestDir('extractTo/subDir');
        $extractToPath = $this->getPathToTestFile('extractTo/subDir/downloadTo.rar');
        $extractTo = new SplFileInfo($extractToDir);

        $state = $this->getMockBuilder(State::class)->getMock();
        $state->method('getParameter')->will($this->returnCallback(function ($name) use ($downloadTo, $extractTo) {
            $return = null;
            if ($name === Task::DOWNLOAD_TO_FILE_PARAM) {
                $return = $downloadTo;
            } elseif ($name === Task::EXTRACT_TO_FOLDER_PARAM) {
                $return = $extractTo;
            }

            return $return;
        }));

        $task = new CleanupTask();
        $task->run($state);

        $this->assertFalse(file_exists($downloadToPath), 'Downloaded file removed');
        $this->assertFalse(file_exists($extractToPath), 'Extracted files removed');
    }

    /**
     * Проверяет, что задача очищает все папки и файлы.
     */
    public function testRunEmptyFiles()
    {
        $downloadToPath = __DIR__ . '/test.rar';
        $downloadTo = new SplFileInfo($downloadToPath);

        $state = $this->getMockBuilder(State::class)->getMock();
        $state->method('getParameter')->will($this->returnCallback(function ($name) use ($downloadTo) {
            return $name === Task::DOWNLOAD_TO_FILE_PARAM ? $downloadTo : null;
        }));

        $task = new CleanupTask();
        $task->run($state);

        $this->assertFalse(file_exists($downloadToPath), 'Downloaded file removed');
    }
}
