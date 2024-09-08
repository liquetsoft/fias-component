<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\PrepareFolderTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для задачи, которая подготавливает папки и файлы для импорта.
 *
 * @internal
 */
final class PrepareFolderTaskTest extends BaseCase
{
    /**
     * Проверяет, что задача создает все папки и передает в состояние.
     */
    public function testRun(): void
    {
        $folder = 'prepare';
        $pathToPrepare = $this->getPathToTestDir($folder);

        $state = new ArrayState();

        $task = new PrepareFolderTask($pathToPrepare);
        $task->run($state);
        $downloadFile = $state->getParameterString(StateParameter::PATH_TO_DOWNLOAD_FILE);
        $extractToFolder = $state->getParameterString(StateParameter::PATH_TO_EXTRACT_FOLDER);

        $this->assertStringEndsWith("{$folder}/archive", $downloadFile);
        $this->assertStringEndsWith("{$folder}/extracted", $extractToFolder);
        $this->assertDirectoryExists($extractToFolder);
    }

    /**
     * Проверяет, что задача выбросит исключение, если задана папка, которая
     * не доступна на запись или родительская папка для которой не существует..
     */
    public function testConstructBadFolderException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new PrepareFolderTask('/empty/empty');
    }
}
