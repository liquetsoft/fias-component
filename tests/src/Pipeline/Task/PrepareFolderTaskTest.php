<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\State;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\Task\PrepareFolderTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use SplFileInfo;

/**
 * Тест для задачи, которая подготавливает папки и файлы для импорта.
 */
class PrepareFolderTaskTest extends BaseCase
{
    /**
     * Проверяет, что задача создает все папки и передает в состояние.
     */
    public function testRun()
    {
        $pathToPrepare = $this->getPathToTestDir('prepare');
        $testFile = $this->getPathToTestFile('prepare/test.txt');

        $state = $this->getMockBuilder(State::class)->getMock();
        $state->expects($this->at(0))->method('setAndLockParameter')->with(
            $this->equalTo(Task::DOWNLOAD_TO_FILE_PARAM),
            $this->isInstanceOf(SplFileInfo::class)
        );
        $state->expects($this->at(1))->method('setAndLockParameter')->with(
            $this->equalTo(Task::EXTRACT_TO_FOLDER_PARAM),
            $this->callback(function ($folder) {
                return ($folder instanceof SplFileInfo) && $folder->isDir();
            })
        );

        $task = new PrepareFolderTask($pathToPrepare);
        $task->run($state);
    }

    /**
     * Проверяет, что задача выбросит исключение, если задана папка, которая
     * не доступна на запись или родительская папка для которой не существует..
     */
    public function testConstructBadFolderException()
    {
        $this->expectException(InvalidArgumentException::class);
        $task = new PrepareFolderTask(__DIR__ . '/empty/empty');
    }
}
