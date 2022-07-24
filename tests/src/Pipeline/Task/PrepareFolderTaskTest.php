<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Exception;
use InvalidArgumentException;
use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\Task\PrepareFolderTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use SplFileInfo;

/**
 * Тест для задачи, которая подготавливает папки и файлы для импорта.
 *
 * @internal
 */
class PrepareFolderTaskTest extends BaseCase
{
    /**
     * Проверяет, что задача создает все папки и передает в состояние.
     *
     * @throws Exception
     */
    public function testRun(): void
    {
        $pathToPrepare = $this->getPathToTestDir('prepare');
        $this->getPathToTestFile('prepare/test.txt');

        $state = new ArrayState();

        $task = new PrepareFolderTask($pathToPrepare);
        $task->run($state);
        $downloadFile = $state->getParameter(Task::DOWNLOAD_TO_FILE_PARAM);
        $extractToFolder = $state->getParameter(Task::EXTRACT_TO_FOLDER_PARAM);

        $this->assertInstanceOf(SplFileInfo::class, $downloadFile);
        $this->assertInstanceOf(SplFileInfo::class, $extractToFolder);
        $this->assertTrue($extractToFolder->isDir());
    }

    /**
     * Проверяет, что задача выбросит исключение, если задана папка, которая
     * не доступна на запись или родительская папка для которой не существует..
     */
    public function testConstructBadFolderException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PrepareFolderTask(__DIR__ . '/empty/empty');
    }
}
