<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Exception;
use InvalidArgumentException;
use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
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
        $downloadFile = $state->getParameter(StateParameter::DOWNLOAD_TO_FILE);
        $extractToFolder = $state->getParameter(StateParameter::EXTRACT_TO_FOLDER);

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
