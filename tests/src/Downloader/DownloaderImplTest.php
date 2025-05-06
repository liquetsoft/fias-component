<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Downloader;

use Liquetsoft\Fias\Component\Downloader\DownloaderImpl;
use Liquetsoft\Fias\Component\Exception\DownloaderException;
use Liquetsoft\Fias\Component\HttpTransport\HttpTransport;
use Liquetsoft\Fias\Component\HttpTransport\HttpTransportResponse;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для строенной реализации загрузчика.
 *
 * @internal
 */
final class DownloaderImplTest extends BaseCase
{
    private const URL = 'https://test.ru/test.zip';
    private const METHOD_HEAD = 'head';
    private const METHOD_DOWNLOAD = 'download';

    /**
     * Проверяет обычную загрузку.
     */
    public function testDownload(): void
    {
        $path = $this->getPathToTestFile('testDownload.txt');
        $destination = $this->createSplFileInfoMock($path);
        $okResponse = $this->createOkResponseMock();

        $transport = $this->mock(HttpTransport::class);
        $transport->expects($this->once())
            ->method(self::METHOD_HEAD)
            ->with(
                $this->equalTo(self::URL)
            )
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

        $downloader = new DownloaderImpl($transport, 1);
        $downloader->download(self::URL, $destination);
    }

    /**
     * Проверяет, что объект попробует повторить загрузку в случае ошибки.
     */
    public function testDownloadWithRetry(): void
    {
        $path = $this->getPathToTestFile('testDownload.txt');
        $destination = $this->createSplFileInfoMock($path);
        $okResponse = $this->createOkResponseMock();
        $badResponse = $this->createBadResponseMock();

        $transport = $this->mock(HttpTransport::class);
        $transport->expects($this->once())
            ->method(self::METHOD_HEAD)
            ->willReturn($okResponse);

        $tries = 0;
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
                function () use (&$tries, $okResponse, $badResponse): HttpTransportResponse {
                    ++$tries;

                    return $tries === 4 ? $okResponse : $badResponse;
                }
            );

        $downloader = new DownloaderImpl($transport, 4);
        $downloader->download(self::URL, $destination);
    }

    /**
     * Проверяет, что объект попробует повторить загрузку в случае ошибки с того момента,
     * где она остановилась при ошибке.
     */
    public function testDownloadWithRetryAndRange(): void
    {
        $path = $this->getPathToTestFile('testDownload.txt');
        $destination = $this->createSplFileInfoMock($path);
        $bytesFrom = 10;
        $bytesTo = 99;
        $headResponse = $this->createHeadResponseMock(true, $bytesTo + 1);
        $okResponse = $this->createOkResponseMock();
        $badResponse = $this->createBadResponseMock();

        $transport = $this->mock(HttpTransport::class);
        $transport->expects($this->once())
            ->method(self::METHOD_HEAD)
            ->willReturn($headResponse);

        $tries = 0;
        $transport->expects($this->exactly(2))
            ->method(self::METHOD_DOWNLOAD)
            ->willReturnCallback(
                function (string $url, mixed $fh, ?int $from, ?int $to) use (&$tries, $bytesFrom, $bytesTo, $okResponse, $badResponse): HttpTransportResponse {
                    ++$tries;
                    if ($tries === 1 && \is_resource($fh)) {
                        fwrite($fh, str_repeat('-', $bytesFrom));
                    }
                    if ($tries === 2 && $from === $bytesFrom && $to === $bytesTo) {
                        return $okResponse;
                    } else {
                        return $badResponse;
                    }
                }
            );

        $downloader = new DownloaderImpl($transport, 3);
        $downloader->download(self::URL, $destination);
    }

    /**
     * Проверяет, что объект не будет пробовать возобновить загрузку с того же места,
     * если сервер этого не поддерживает.
     */
    public function testDownloadWithRetryAndRangeNotSupported(): void
    {
        $path = $this->getPathToTestFile('testDownload.txt');
        $destination = $this->createSplFileInfoMock($path);
        $headResponse = $this->createHeadResponseMock();
        $okResponse = $this->createOkResponseMock();
        $badResponse = $this->createBadResponseMock();

        $transport = $this->mock(HttpTransport::class);
        $transport->expects($this->once())->method(self::METHOD_HEAD)->willReturn($headResponse);

        $tries = 0;
        $transport->expects($this->exactly(2))
            ->method(self::METHOD_DOWNLOAD)
            ->with(
                $this->anything(),
                $this->anything(),
                $this->isNull(),
                $this->isNull()
            )
            ->willReturnCallback(
                function (string $url, mixed $fh) use (&$tries, $okResponse, $badResponse): HttpTransportResponse {
                    ++$tries;
                    if ($tries === 1 && \is_resource($fh)) {
                        fwrite($fh, '123');
                    }
                    if ($tries === 2) {
                        return $okResponse;
                    } else {
                        return $badResponse;
                    }
                }
            );

        $downloader = new DownloaderImpl($transport, 10);
        $downloader->download(self::URL, $destination);
    }

    /**
     * Проверяет, что объект перехватит исключение при вызове head.
     */
    public function testDownloadHeadException(): void
    {
        $path = $this->getPathToTestFile('testDownloadHeadException');
        $destination = $this->createSplFileInfoMock($path);
        $exception = new \RuntimeException('message for exception');

        $transport = $this->mock(HttpTransport::class);
        $transport->expects($this->once())->method(self::METHOD_HEAD)->willThrowException($exception);
        $transport->expects($this->never())->method(self::METHOD_DOWNLOAD);

        $downloader = new DownloaderImpl($transport, 10);

        $this->expectException(DownloaderException::class);
        $this->expectExceptionMessage($exception->getMessage());
        $downloader->download(self::URL, $destination);
    }

    /**
     * Проверяет, что объект перехватит исключение при вызове download.
     */
    public function testDownloadException(): void
    {
        $path = $this->getPathToTestFile('testDownloadException');
        $destination = $this->createSplFileInfoMock($path);
        $exception = new \RuntimeException('message for exception');
        $okResponse = $this->createOkResponseMock();

        $transport = $this->mock(HttpTransport::class);
        $transport->expects($this->once())->method(self::METHOD_HEAD)->willReturn($okResponse);
        $transport->expects($this->exactly(2))->method(self::METHOD_DOWNLOAD)->willThrowException($exception);

        $downloader = new DownloaderImpl($transport, 2);

        $this->expectException(DownloaderException::class);
        $this->expectExceptionMessage($exception->getMessage());
        $downloader->download(self::URL, $destination);
    }

    /**
     * Проверяет, что объект выбросит исключение, при 500 статусе.
     */
    public function testDownloadBadStatusException(): void
    {
        $path = $this->getPathToTestFile('testDownloadBadStatusException');
        $destination = $this->createSplFileInfoMock($path);
        $okResponse = $this->createOkResponseMock();
        $badResponse = $this->createBadResponseMock();

        $transport = $this->mock(HttpTransport::class);
        $transport->expects($this->once())->method(self::METHOD_HEAD)->willReturn($okResponse);
        $transport->expects($this->exactly(2))->method(self::METHOD_DOWNLOAD)->willReturn($badResponse);

        $downloader = new DownloaderImpl($transport, 2);

        $this->expectException(DownloaderException::class);
        $this->expectExceptionMessage((string) self::STATUS_SERVER_ERROR);
        $downloader->download(self::URL, $destination);
    }

    /**
     * Проверяет, что объект выбросит исключение, если указана битая ссылка.
     *
     * @dataProvider provideDownloadMalformedUrlException
     */
    public function testDownloadMalformedUrlException(string $url): void
    {
        $path = $this->getPathToTestFile('testDownloadMalformedUrlException');
        $destination = $this->createSplFileInfoMock($path);
        $transport = $this->mock(HttpTransport::class);

        $downloader = new DownloaderImpl($transport);

        $this->expectException(DownloaderException::class);
        $this->expectExceptionMessage("Empty or malformed url '{$url}' provided");
        $downloader->download($url, $destination);
    }

    public static function provideDownloadMalformedUrlException(): array
    {
        return [
            'malformed url' => ['testhttp://test.test'],
            'no protocol' => ['test.test/test'],
            'simple text' => ['text'],
            'empty url' => [''],
        ];
    }

    /**
     * Создает мок для файла.
     *
     * @return \SplFileInfo&MockObject
     */
    private function createSplFileInfoMock(string $name = '', int $size = 0): \SplFileInfo
    {
        $file = $this->mock(\SplFileInfo::class);
        $file->method('getPathname')->willReturn($name);
        $file->method('getRealPath')->willReturn($name);
        $file->method('getPath')->willReturn($name);
        $file->method('getSize')->willReturn($size);
        $file->method('isFile')->willReturn(true);
        $file->method('isDir')->willReturn(false);

        return $file;
    }

    /**
     * Создает мок с ответом на HEAD запрос.
     *
     * @return HttpTransportResponse&MockObject
     */
    private function createHeadResponseMock(bool $acceptRanges = false, int $contentLength = 0): HttpTransportResponse
    {
        $headers = [];
        if ($acceptRanges) {
            $headers['content-length'] = $contentLength;
            $headers['accept-ranges'] = 'bytes';
        }

        return $this->createResponseMock(200, $headers);
    }
}
