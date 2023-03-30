<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\Task\PrepareFolderTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\FileSystemCase;
use Liquetsoft\Fias\Component\Tests\LoggerCase;
use Liquetsoft\Fias\Component\Tests\PipelineCase;
use Psr\Log\LogLevel;

/**
 * Тест для задачи, которая проверяет доступность сервисов ФИАС.
 *
 * @internal
 */
class PrepareFolderTaskTest extends BaseCase
{
    use PipelineCase;
    use LoggerCase;
    use FileSystemCase;

    /**
     * Проверяет, что объект подготовит папку для ФИАС.
     */
    public function testRun(): void
    {
        $path = '/test/test';
        $folder = $this->createSplDirInfoMock($path);

        $fs = $this->createFileSystemMock();
        $fs->expects($this->once())->method('mkdirIfNotExist')->with($this->identicalTo($folder));
        $fs->expects($this->once())->method('emptyDir')->with($this->identicalTo($folder));
        $fs->expects($this->once())
            ->method('mkdir')
            ->with(
                $this->callback(
                    fn (string $file): bool => str_starts_with($file, $path)
                )
            );

        $newState = $this->createPipelineStateMock();
        $state = $this->createPipelineStateMock();
        $state->expects($this->once())
            ->method('withList')
            ->with(
                $this->callback(
                    fn (array $params): bool => isset($params[PipelineStateParam::DOWNLOAD_TO_FILE->value])
                        && \is_string($params[PipelineStateParam::DOWNLOAD_TO_FILE->value])
                        && str_starts_with($params[PipelineStateParam::DOWNLOAD_TO_FILE->value], $path)
                        && isset($params[PipelineStateParam::EXTRACT_TO_FOLDER->value])
                        && \is_string($params[PipelineStateParam::EXTRACT_TO_FOLDER->value])
                        && str_starts_with($params[PipelineStateParam::EXTRACT_TO_FOLDER->value], $path)
                )
            )
            ->willReturn($newState);

        $logger = $this->createLoggerMockExpectsMessage(LogLevel::INFO, $path);

        $task = new PrepareFolderTask($fs, $folder);
        $task->injectLogger($logger);
        $stateToTest = $task->run($state);

        $this->assertSame($newState, $stateToTest);
    }
}
