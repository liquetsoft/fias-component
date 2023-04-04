<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelector;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\Task\SelectFilesFromArchiveTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\FileSystemCase;
use Liquetsoft\Fias\Component\Tests\LoggerCase;
use Liquetsoft\Fias\Component\Tests\PipelineCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

/**
 * Тест для задачи, которая собирает список файлов для загрузки из архива.
 *
 * @internal
 */
class SelectFilesFromArchiveTaskTest extends BaseCase
{
    use PipelineCase;
    use LoggerCase;
    use FileSystemCase;

    /**
     * Проверяет, что объект соберет файлы из архива.
     */
    public function testRun(): void
    {
        $archivePath = '/test/archive.zip';
        $archive = $this->createSplFileInfoMock($archivePath);

        $selecteFilePath = '/test/selected.txt';
        /** @var MockObject&FiasFileSelectorFile */
        $selectedFile = $this->getMockBuilder(FiasFileSelectorFile::class)->getMock();
        $selectedFile->method('getPath')->willReturn($selecteFilePath);
        $selectedFile->method('getPathToArchive')->willReturn($archivePath);
        $selectedFile->method('isArchived')->willReturn(true);

        /** @var MockObject&FiasFileSelector */
        $selector = $this->getMockBuilder(FiasFileSelector::class)->getMock();
        $selector->expects($this->once())
            ->method('select')
            ->with($this->identicalTo($archive))
            ->willReturn([$selectedFile]);

        $fs = $this->createFileSystemMock();
        $fs->expects($this->once())
            ->method('makeFileInfo')
            ->with($this->identicalTo($archivePath))
            ->willReturn($archive);

        $newState = $this->createPipelineStateMock();
        $state = $this->createPipelineStateMock(
            [
                PipelineStateParam::DOWNLOAD_TO_FILE->value => $archivePath,
            ]
        );
        $state->expects($this->once())
            ->method('with')
            ->with(
                $this->identicalTo(PipelineStateParam::FILES_TO_PROCEED),
                $this->identicalTo([$selectedFile])
            )
            ->willReturn($newState);

        $logger = $this->createLoggerMockExpectsMessage(
            LogLevel::INFO,
            'Files selected from archive',
            [
                'archive' => $archivePath,
                'files' => [$selecteFilePath],
            ]
        );

        $task = new SelectFilesFromArchiveTask($selector, $fs);
        $task->injectLogger($logger);
        $stateToTest = $task->run($state);

        $this->assertSame($newState, $stateToTest);
    }
}
