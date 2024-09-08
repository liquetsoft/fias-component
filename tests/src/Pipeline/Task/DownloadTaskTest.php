<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Downloader\Downloader;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\DownloadTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для задачи, которая загружает архив ФИАС по ссылке.
 *
 * @internal
 */
final class DownloadTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект верно загружает ссылку.
     */
    public function testRun(): void
    {
        $url = 'https://test.test/test';
        $filePath = '/test.file';

        $downloader = $this->mock(Downloader::class);
        $downloader->expects($this->once())
            ->method('download')->with(
                $this->equalTo($url),
                $this->callback(
                    fn (\SplFileInfo $file): bool => $file->getPathname() === $filePath
                )
            );

        $state = $this->createDefaultStateMock(
            [
                StateParameter::FIAS_VERSION_ARCHIVE_URL->value => $url,
                StateParameter::PATH_TO_DOWNLOAD_FILE->value => $filePath,
            ]
        );

        $task = new DownloadTask($downloader);

        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указана ссылка на ФИАС.
     */
    public function testRunNoFiasInfoException(): void
    {
        $downloader = $this->mock(Downloader::class);

        $state = $this->createDefaultStateMock(
            [
                StateParameter::PATH_TO_DOWNLOAD_FILE->value => '/test.file',
            ]
        );

        $task = new DownloadTask($downloader);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указан путь к локальному файлу.
     */
    public function testRunNoDownloadToInfoException(): void
    {
        $downloader = $this->mock(Downloader::class);

        $state = $this->createDefaultStateMock(
            [
                StateParameter::FIAS_VERSION_ARCHIVE_URL->value => 'https://test.test/test',
            ]
        );

        $task = new DownloadTask($downloader);

        $this->expectException(TaskException::class);
        $task->run($state);
    }
}
