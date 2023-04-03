<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\Task\CleanupTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\FileSystemCase;
use Liquetsoft\Fias\Component\Tests\LoggerCase;
use Liquetsoft\Fias\Component\Tests\PipelineCase;
use Psr\Log\LogLevel;

/**
 * Тест для задачи, которая очищает временные файлы после работы пайплайна.
 *
 * @internal
 */
class CleanupTaskTest extends BaseCase
{
    use PipelineCase;
    use LoggerCase;
    use FileSystemCase;

    /**
     * Проверяет, что объект удалит все временные файлы.
     */
    public function testRun(): void
    {
        $pathDownloadFile = '/test/download';
        $pathExtractToFolder = '/test/extract';
        $extractToFolder = $this->createSplDirInfoMock($pathExtractToFolder);

        $fs = $this->createFileSystemMock();
        $fs->expects($this->exactly(2))
            ->method('removeIfExists')
            ->with(
                $this->callback(
                    fn (string|\SplFileInfo $file): bool => $file === $pathDownloadFile || $file === $extractToFolder
                )
            );

        $state = $this->createPipelineStateMock(
            [
                PipelineStateParam::DOWNLOAD_TO_FILE->value => $pathDownloadFile,
                PipelineStateParam::EXTRACT_TO_FOLDER->value => $extractToFolder,
            ]
        );

        $logger = $this->createLoggerMock();
        $logger->expects($this->exactly(2))
            ->method('log')
            ->with(
                $this->identicalTo(LogLevel::INFO),
                $this->identicalTo('Path cleaned')
            );

        $task = new CleanupTask($fs);
        $task->injectLogger($logger);
        $stateToTest = $task->run($state);

        $this->assertSame($state, $stateToTest);
    }

    /**
     * Проверяет, что объект не выбросит ошибки, если не указаны файлы на удаление.
     */
    public function testRunNoParams(): void
    {
        $fs = $this->createFileSystemMock();
        $fs->expects($this->never())->method('removeIfExists');

        $state = $this->createPipelineStateMock();

        $task = new CleanupTask($fs);
        $stateToTest = $task->run($state);

        $this->assertSame($state, $stateToTest);
    }
}
