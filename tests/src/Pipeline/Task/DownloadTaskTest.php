<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Exception;
use Liquetsoft\Fias\Component\Downloader\Downloader;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasInformer\InformerResponse;
use Liquetsoft\Fias\Component\Pipeline\Task\DownloadTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use SplFileInfo;

/**
 * Тест для задачи, которая загружает архив ФИАС по ссылке.
 */
class DownloadTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект верно загружает ссылку.
     *
     * @throws Exception
     */
    public function testRun()
    {
        $url = $this->createFakeData()->url;

        $informerResult = $this->getMockBuilder(InformerResponse::class)->getMock();
        $informerResult->method('hasResult')->willReturn(true);
        $informerResult->method('getUrl')->willReturn($url);

        $filePath = __DIR__ . '/test.file';
        $file = new SplFileInfo($filePath);

        $downloader = $this->getMockBuilder(Downloader::class)->getMock();
        $downloader->expects($this->once())
            ->method('download')->with(
                $this->equalTo($url),
                $this->callback(
                    function ($file) use ($filePath) {
                        return $file->getPathname() === $filePath;
                    }
                )
            );
        $downloader = $this->checkAndReturnDownloader($downloader);

        $state = $this->createDefaultStateMock(
            [
                Task::FIAS_INFO_PARAM => $informerResult,
                Task::DOWNLOAD_TO_FILE_PARAM => $file,
            ]
        );

        $task = new DownloadTask($downloader);

        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указана ссылка на ФИАС.
     *
     * @throws Exception
     */
    public function testRunNoFiasInfoException()
    {
        $downloader = $this->getMockBuilder(Downloader::class)->getMock();
        $downloader = $this->checkAndReturnDownloader($downloader);

        $state = $this->createDefaultStateMock(
            [
                Task::DOWNLOAD_TO_FILE_PARAM => new SplFileInfo(__DIR__ . '/test.file'),
            ]
        );

        $task = new DownloadTask($downloader);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указан путь к локальному файлу.
     *
     * @throws Exception
     */
    public function testRunNoDownloadToInfoException()
    {
        $downloader = $this->getMockBuilder(Downloader::class)->getMock();
        $downloader = $this->checkAndReturnDownloader($downloader);

        $informerResult = $this->getMockBuilder(InformerResponse::class)->getMock();
        $informerResult->method('hasResult')->willReturn(true);
        $informerResult->method('getUrl')->willReturn($this->createFakeData()->url);

        $state = $this->createDefaultStateMock(
            [
                Task::FIAS_INFO_PARAM => $informerResult,
            ]
        );

        $task = new DownloadTask($downloader);

        $this->expectException(TaskException::class);
        $task->run($state);
    }
}
