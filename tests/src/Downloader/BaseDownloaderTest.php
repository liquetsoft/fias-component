<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Downloader;

use Liquetsoft\Fias\Component\Downloader\BaseDownloader;
use Liquetsoft\Fias\Component\Exception\DownloaderException;
use Liquetsoft\Fias\Component\HttpTransport\HttpResponse;
use Liquetsoft\Fias\Component\HttpTransport\HttpResponseFactory;
use Liquetsoft\Fias\Component\HttpTransport\HttpTransport;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для строенной реализации загрузчика.
 *
 * @internal
 */
class BaseDownloaderTest extends BaseCase
{
    private const URL = 'https://test.ru/test.zip';
    private const STATUS_OK = 200;
    private const STATUS_SERVER_ERROR = 500;
    private const METHOD_HEAD = 'head';
    private const METHOD_DOWNLOAD = 'download';

    /**
     * Проверяет обычную загрузку.
     */
    public function testDownload(): void
    {
        $path = $this->getPathToTestFile('testDownload.txt');
        $destination = new \SplFileInfo($path);
        $okResponse = HttpResponseFactory::create(self::STATUS_OK);

        /** @var HttpTransport&MockObject */
        $transport = $this->getMockBuilder(HttpTransport::class)->getMock();
        $transport->expects($this->once())
            ->method(self::METHOD_HEAD)
            ->with($this->equalTo(self::URL))
            ->willReturn($okResponse);
        $transport->expects($this->once())
            ->method(self::METHOD_DOWNLOAD)
            ->with(
                $this->equalTo(self::URL),
                $this->callback(
                    fn ($fh): bool => \is_resource($fh) && stream_get_meta_data($fh)['uri'] === $path
                ),
                $this->isNull(),
                $this->isNull()
            )
            ->willReturn($okResponse);

        $downloader = new BaseDownloader($transport, 1);
        $downloader->download(self::URL, $destination);
    }

    /**
     * Проверяет, что объект попробует повторить загрузку в случае ошибки.
     */
    public function testDownloadWithRetry(): void
    {
        $path = $this->getPathToTestFile('testDownload.txt');
        $destination = new \SplFileInfo($path);
        $okResponse = HttpResponseFactory::create(self::STATUS_OK);
        $badResponse = HttpResponseFactory::create(self::STATUS_SERVER_ERROR);

        /** @var HttpTransport&MockObject */
        $transport = $this->getMockBuilder(HttpTransport::class)->getMock();
        $transport->expects($this->once())->method(self::METHOD_HEAD)->willReturn($okResponse);
        $transport->expects($this->exactly(4))
            ->method(self::METHOD_DOWNLOAD)
            ->with(
                $this->equalTo(self::URL),
                $this->callback(
                    fn ($fh): bool => \is_resource($fh) && stream_get_meta_data($fh)['uri'] === $path
                ),
                $this->isNull(),
                $this->isNull()
            )
            ->willReturnCallback(
                fn (): HttpResponse => match ($this->incrementAndGetCounter()) {
                    4 => $okResponse,
                    default => $badResponse,
                }
            );

        $downloader = new BaseDownloader($transport, 4);
        $downloader->download(self::URL, $destination);
    }

    /**
     * Проверяет, что объект попробует повторить загрузку в случае ошибки с того момента,
     * где она остановилась при ошибке.
     */
    public function testDownloadWithRetryAndRange(): void
    {
        $path = $this->getPathToTestFile('testDownload.txt');
        $destination = new \SplFileInfo($path);
        $bytesFrom = 10;
        $bytesTo = 99;
        $headResponse = HttpResponseFactory::create(
            self::STATUS_OK,
            [
                'Content-Length' => $bytesTo + 1,
                'Accept-Ranges' => 'bytes',
            ]
        );

        /** @var HttpTransport&MockObject */
        $transport = $this->getMockBuilder(HttpTransport::class)->getMock();
        $transport->expects($this->once())->method(self::METHOD_HEAD)->willReturn($headResponse);
        $transport->expects($this->exactly(2))
            ->method(self::METHOD_DOWNLOAD)
            ->willReturnCallback(
                function (string $url, mixed $fh, ?int $from, ?int $to) use ($bytesFrom, $bytesTo): HttpResponse {
                    $counter = $this->incrementAndGetCounter();
                    if ($counter === 1 && \is_resource($fh)) {
                        fwrite($fh, str_repeat('-', $bytesFrom));
                    }
                    if ($counter === 2 && $from === $bytesFrom && $to === $bytesTo) {
                        return HttpResponseFactory::create(self::STATUS_OK);
                    } else {
                        return HttpResponseFactory::create(self::STATUS_SERVER_ERROR);
                    }
                }
            );

        $downloader = new BaseDownloader($transport, 3);
        $downloader->download(self::URL, $destination);
    }

    /**
     * Проверяет, что объект не будет пробовать возобновить загрузку с того же места,
     * если сервер этого не поддерживает.
     */
    public function testDownloadWithRetryAndRangeNotSupported(): void
    {
        $path = $this->getPathToTestFile('testDownload.txt');
        $destination = new \SplFileInfo($path);

        /** @var HttpTransport&MockObject */
        $transport = $this->getMockBuilder(HttpTransport::class)->getMock();
        $transport->expects($this->once())->method(self::METHOD_HEAD)->willReturn(HttpResponseFactory::create(self::STATUS_OK));
        $transport->expects($this->exactly(2))
            ->method(self::METHOD_DOWNLOAD)
            ->with(
                $this->anything(),
                $this->anything(),
                $this->isNull(),
                $this->isNull()
            )
            ->willReturnCallback(
                function (string $url, mixed $fh): HttpResponse {
                    $counter = $this->incrementAndGetCounter();
                    if ($counter === 1 && \is_resource($fh)) {
                        fwrite($fh, '123');
                    }
                    if ($counter === 2) {
                        return HttpResponseFactory::create(self::STATUS_OK);
                    } else {
                        return HttpResponseFactory::create(self::STATUS_SERVER_ERROR);
                    }
                }
            );

        $downloader = new BaseDownloader($transport, 10);
        $downloader->download(self::URL, $destination);
    }

    /**
     * Проверяет, что объект перехватит исключение при вызове head.
     */
    public function testDownloadHeadException(): void
    {
        $destination = new \SplFileInfo($this->getPathToTestFile('testDownloadHeadException'));
        $exception = new \RuntimeException('message for exception');

        /** @var HttpTransport&MockObject */
        $transport = $this->getMockBuilder(HttpTransport::class)->getMock();
        $transport->expects($this->once())->method(self::METHOD_HEAD)->willThrowException($exception);
        $transport->expects($this->never())->method(self::METHOD_DOWNLOAD);

        $downloader = new BaseDownloader($transport, 10);

        $this->expectException(DownloaderException::class);
        $this->expectExceptionMessage($exception->getMessage());
        $downloader->download(self::URL, $destination);
    }

    /**
     * Проверяет, что объект перехватит исключение при вызове download.
     */
    public function testDownloadException(): void
    {
        $destination = new \SplFileInfo($this->getPathToTestFile('testDownloadException'));
        $exception = new \RuntimeException('message for exception');
        $okResponse = HttpResponseFactory::create(self::STATUS_OK);

        /** @var HttpTransport&MockObject */
        $transport = $this->getMockBuilder(HttpTransport::class)->getMock();
        $transport->expects($this->once())->method(self::METHOD_HEAD)->willReturn($okResponse);
        $transport->expects($this->exactly(2))->method(self::METHOD_DOWNLOAD)->willThrowException($exception);

        $downloader = new BaseDownloader($transport, 2);

        $this->expectException(DownloaderException::class);
        $this->expectExceptionMessage($exception->getMessage());
        $downloader->download(self::URL, $destination);
    }

    /**
     * Проверяет, что объект выбросит исключение, при 500 статусе.
     */
    public function testDownloadBadStatusException(): void
    {
        $destination = new \SplFileInfo($this->getPathToTestFile('testDownloadBadStatusException'));
        $okResponse = HttpResponseFactory::create(self::STATUS_OK);
        $badResponse = HttpResponseFactory::create(self::STATUS_SERVER_ERROR);

        /** @var HttpTransport&MockObject */
        $transport = $this->getMockBuilder(HttpTransport::class)->getMock();
        $transport->expects($this->once())->method(self::METHOD_HEAD)->willReturn($okResponse);
        $transport->expects($this->exactly(2))->method(self::METHOD_DOWNLOAD)->willReturn($badResponse);

        $downloader = new BaseDownloader($transport, 2);

        $this->expectException(DownloaderException::class);
        $this->expectExceptionMessage((string) self::STATUS_SERVER_ERROR);
        $downloader->download(self::URL, $destination);
    }
}
