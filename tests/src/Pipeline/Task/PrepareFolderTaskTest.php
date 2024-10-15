<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\PrepareFolderTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Marvin255\FileSystemHelper\FileSystemHelper;

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
        $folder = '/prepare';

        $fs = $this->mock(FileSystemHelper::class);
        $fs->expects($this->once())
            ->method('mkdirIfNotExist')
            ->with(
                $this->callback(
                    fn (\SplFileInfo $f): bool => $f->getPathname() === $folder
                )
            );
        $fs->expects($this->once())
            ->method('emptyDir')
            ->with(
                $this->callback(
                    fn (\SplFileInfo $f): bool => $f->getPathname() === $folder
                )
            );
        $fs->expects($this->once())
            ->method('mkdir')
            ->with(
                $this->callback(
                    fn (\SplFileInfo $f): bool => $f->getPathname() === '/extracted'
                )
            );

        $state = $this->createStateMock();

        $task = new PrepareFolderTask($folder, $fs);
        $newState = $task->run($state);
        $downloadFile = $newState->getParameterString(StateParameter::PATH_TO_DOWNLOAD_FILE);
        $extractToFolder = $newState->getParameterString(StateParameter::PATH_TO_EXTRACT_FOLDER);

        $this->assertStringEndsWith('/archive', $downloadFile);
        $this->assertStringEndsWith('/extracted', $extractToFolder);
    }
}
