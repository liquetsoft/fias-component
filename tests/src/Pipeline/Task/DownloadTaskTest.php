<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Downloader\Downloader;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasInformer\InformerResponse;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\DownloadTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для задачи, которая загружает архив ФИАС по ссылке.
 *
 * @internal
 */
class DownloadTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект верно загружает ссылку.
     *
     * @throws \Exception
     */
    public function testRun(): void
    {
        $url = $this->createFakeData()->url();

        $informerResult = $this->getMockBuilder(InformerResponse::class)->getMock();
        $informerResult->method('getFullUrl')->willReturn($url);

        $filePath = __DIR__ . '/test.file';
        $file = new \SplFileInfo($filePath);

        /** @var MockObject&Downloader */
        $downloader = $this->getMockBuilder(Downloader::class)->getMock();
        $downloader->expects($this->once())
            ->method('download')->with(
                $this->equalTo($url),
                $this->callback(
                    function (\SplFileInfo $file) use ($filePath) {
                        return $file->getPathname() === $filePath;
                    }
                )
            );

        $state = $this->createDefaultStateMock(
            [
                StateParameter::FIAS_INFO => $informerResult,
                StateParameter::DOWNLOAD_TO_FILE => $file,
            ]
        );

        $task = new DownloadTask($downloader);

        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указана ссылка на ФИАС.
     *
     * @throws \Exception
     */
    public function testRunNoFiasInfoException(): void
    {
        /** @var MockObject&Downloader */
        $downloader = $this->getMockBuilder(Downloader::class)->getMock();

        $state = $this->createDefaultStateMock(
            [
                StateParameter::DOWNLOAD_TO_FILE => new \SplFileInfo(__DIR__ . '/test.file'),
            ]
        );

        $task = new DownloadTask($downloader);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указан путь к локальному файлу.
     *
     * @throws \Exception
     */
    public function testRunNoDownloadToInfoException(): void
    {
        /** @var MockObject&Downloader */
        $downloader = $this->getMockBuilder(Downloader::class)->getMock();

        $informerResult = $this->getMockBuilder(InformerResponse::class)->getMock();
        $informerResult->method('getFullUrl')->willReturn($this->createFakeData()->url());

        $state = $this->createDefaultStateMock(
            [
                StateParameter::FIAS_INFO => $informerResult,
            ]
        );

        $task = new DownloadTask($downloader);

        $this->expectException(TaskException::class);
        $task->run($state);
    }
}
