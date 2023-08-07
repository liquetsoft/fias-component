<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Downloader\Downloader;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\Task\DownloadTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\FileSystemCase;
use Liquetsoft\Fias\Component\Tests\LoggerCase;
use Liquetsoft\Fias\Component\Tests\PipelineCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

/**
 * Тест для задачи, которая загружает файл по указанной ссылке.
 *
 * @internal
 */
class DownloadTaskTest extends BaseCase
{
    use PipelineCase;
    use LoggerCase;
    use FileSystemCase;

    /**
     * Проверяет, что объект удалит все временные файлы.
     */
    public function testRun(): void
    {
        $archiveUrl = 'https://test.test/test';
        $localFilePath = '/test/file.txt';
        $localFile = $this->createSplFileInfoMock($localFilePath);

        /** @var MockObject&Downloader */
        $downloader = $this->getMockBuilder(Downloader::class)->getMock();
        $downloader->expects($this->once())
            ->method('download')
            ->with(
                $this->identicalTo($archiveUrl),
                $this->identicalTo($localFile)
            );

        $fs = $this->createFileSystemMock();
        $fs->expects($this->once())
            ->method('makeFileInfo')
            ->with($this->identicalTo($localFilePath))
            ->willReturn($localFile);

        $state = $this->createPipelineStateMock(
            [
                PipelineStateParam::ARCHIVE_URL->value => $archiveUrl,
                PipelineStateParam::DOWNLOAD_TO_FILE->value => $localFilePath,
            ]
        );

        $logger = $this->createLoggerMock();
        $logger->expects($this->exactly(2))
            ->method('log')
            ->with(
                $this->identicalTo(LogLevel::INFO),
                $this->anything(),
                $this->callback(
                    fn (array $p): bool => isset($p['download_url'], $p['download_local_file'])
                        && $p['download_url'] === $archiveUrl
                        && $p['download_local_file'] === $localFilePath
                )
            );

        $task = new DownloadTask($downloader, $fs);
        $task->injectLogger($logger);
        $stateToTest = $task->run($state);

        $this->assertSame($state, $stateToTest);
    }
}
