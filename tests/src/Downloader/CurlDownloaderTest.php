<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Downloader;

use Liquetsoft\Fias\Component\Downloader\CurlDownloader;
use Liquetsoft\Fias\Component\Downloader\CurlTransport;
use Liquetsoft\Fias\Component\Downloader\HttpResponse;
use Liquetsoft\Fias\Component\Exception\DownloaderException;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для объекта, который загружает файл с помощью curl.
 *
 * @internal
 */
class CurlDownloaderTest extends BaseCase
{
    private const KEY_OPTIONS = 'options';
    private const KEY_CODE = 'code';
    private const KEY_HEADERS = 'headers';
    private const KEY_RESPONSE = 'response';
    private const KEY_FILE_CONTENT = 'file_content';
    private const KEY_CURL_EXCEPTION = 'curl_exception';
    private const STATUS_OK = 200;
    private const STATUS_SERVER_ERROR = 500;

    public function testDownload(): void
    {
        $url = 'https://test.ru/test.zip';
        $destinationPath = $this->getPathToTestFile('testDownload.txt');
        $destination = new \SplFileInfo($destinationPath);
        $content = 'testDownload content';

        $transport = $this->createTransportMock(
            [
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_HEADER => true,
                        \CURLOPT_NOBODY => true,
                        \CURLOPT_RETURNTRANSFER => true,
                        \CURLOPT_URL => $url,
                    ],
                ],
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_FOLLOWLOCATION => true,
                        \CURLOPT_FRESH_CONNECT => true,
                        \CURLOPT_CONNECTTIMEOUT => CurlDownloader::DEFAULT_CONNECTION_TIMEOUT,
                        \CURLOPT_TIMEOUT => CurlDownloader::DEFAULT_TIMEOUT,
                        \CURLOPT_URL => $url,
                        \CURLOPT_FILE => $destinationPath,
                    ],
                    self::KEY_FILE_CONTENT => $content,
                ],
            ]
        );

        $downloader = new CurlDownloader([], 10, $transport);
        $downloader->download($url, $destination);

        $this->assertStringEqualsFile($destinationPath, $content);
    }

    public function testDownloadWithAdditionalOptions(): void
    {
        $url = 'https://test.ru/test.zip';
        $destinationPath = $this->getPathToTestFile('testDownloadWithAdditionalOptions.zip');
        $destination = new \SplFileInfo($destinationPath);
        $additionalOptionName = 3333;
        $additionalOptionValue = 3333;

        $transport = $this->createTransportMock(
            [
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_HEADER => true,
                        \CURLOPT_NOBODY => true,
                        \CURLOPT_RETURNTRANSFER => true,
                        \CURLOPT_URL => $url,
                        $additionalOptionName => $additionalOptionValue,
                    ],
                ],
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_FOLLOWLOCATION => true,
                        \CURLOPT_FRESH_CONNECT => true,
                        \CURLOPT_CONNECTTIMEOUT => CurlDownloader::DEFAULT_CONNECTION_TIMEOUT,
                        \CURLOPT_TIMEOUT => CurlDownloader::DEFAULT_TIMEOUT,
                        \CURLOPT_URL => $url,
                        \CURLOPT_FILE => $destinationPath,
                        $additionalOptionName => $additionalOptionValue,
                    ],
                ],
            ]
        );

        $downloader = new CurlDownloader(
            [$additionalOptionName => $additionalOptionValue],
            10,
            $transport
        );
        $downloader->download($url, $destination);
    }

    public function testDownloadWithRetry(): void
    {
        $url = 'https://test.ru/test.zip';
        $destinationPath = $this->getPathToTestFile('testDownloadWithRetry.zip');
        $destination = new \SplFileInfo($destinationPath);
        $content = 'testDownloadWithRetry content';

        $transport = $this->createTransportMock(
            [
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_HEADER => true,
                        \CURLOPT_NOBODY => true,
                        \CURLOPT_URL => $url,
                    ],
                ],
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_URL => $url,
                        \CURLOPT_FILE => $destinationPath,
                        \CURLOPT_RANGE => null,
                    ],
                    self::KEY_CODE => self::STATUS_SERVER_ERROR,
                ],
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_URL => $url,
                        \CURLOPT_FILE => $destinationPath,
                        \CURLOPT_RANGE => null,
                    ],
                    self::KEY_CURL_EXCEPTION => new \RuntimeException(),
                    self::KEY_FILE_CONTENT => '123',
                ],
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_URL => $url,
                        \CURLOPT_FILE => $destinationPath,
                        \CURLOPT_RANGE => null,
                    ],
                    self::KEY_FILE_CONTENT => $content,
                ],
            ]
        );

        $downloader = new CurlDownloader([], 3, $transport);
        $downloader->download($url, $destination);

        $this->assertStringEqualsFile($destinationPath, $content);
    }

    public function testDownloadWithRange(): void
    {
        $url = 'https://test.ru/test.zip';
        $destinationPath = $this->getPathToTestFile('testDownloadWithRange.zip');
        $destination = new \SplFileInfo($destinationPath);
        $partialLoadedContent = 'testDownloadWithRange content';
        $contentLength = \strlen($partialLoadedContent) + 100;

        $transport = $this->createTransportMock(
            [
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_HEADER => true,
                        \CURLOPT_NOBODY => true,
                        \CURLOPT_URL => $url,
                    ],
                    self::KEY_HEADERS => [
                        'content-length' => $contentLength,
                        'accept-ranges' => 'bytes',
                    ],
                ],
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_URL => $url,
                        \CURLOPT_FILE => $destinationPath,
                        \CURLOPT_RANGE => null,
                    ],
                    self::KEY_CODE => self::STATUS_SERVER_ERROR,
                    self::KEY_FILE_CONTENT => $partialLoadedContent,
                ],
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_URL => $url,
                        \CURLOPT_FILE => $destinationPath,
                        \CURLOPT_RANGE => \strlen($partialLoadedContent) . '-' . ($contentLength - 1),
                    ],
                ],
            ]
        );

        $downloader = new CurlDownloader([], 2, $transport);
        $downloader->download($url, $destination);
    }

    public function testDownloadWithRangeNoAcceptRange(): void
    {
        $url = 'https://test.ru/test.zip';
        $destinationPath = $this->getPathToTestFile('testDownloadWithRange.zip');
        $destination = new \SplFileInfo($destinationPath);
        $partialLoadedContent = 'testDownloadWithRange content';
        $contentLength = \strlen($partialLoadedContent) + 100;

        $transport = $this->createTransportMock(
            [
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_HEADER => true,
                        \CURLOPT_NOBODY => true,
                        \CURLOPT_URL => $url,
                    ],
                    self::KEY_HEADERS => [
                        'content-length' => $contentLength,
                        'accept-ranges' => 'test',
                    ],
                ],
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_URL => $url,
                        \CURLOPT_FILE => $destinationPath,
                        \CURLOPT_RANGE => null,
                    ],
                    self::KEY_CODE => self::STATUS_SERVER_ERROR,
                    self::KEY_FILE_CONTENT => $partialLoadedContent,
                ],
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_URL => $url,
                        \CURLOPT_FILE => $destinationPath,
                        \CURLOPT_RANGE => null,
                    ],
                ],
            ]
        );

        $downloader = new CurlDownloader([], 2, $transport);
        $downloader->download($url, $destination);
    }

    public function testDownloadWithRangeEmptyContentLength(): void
    {
        $url = 'https://test.ru/test.zip';
        $destinationPath = $this->getPathToTestFile('testDownloadWithRange.zip');
        $destination = new \SplFileInfo($destinationPath);
        $partialLoadedContent = 'testDownloadWithRange content';

        $transport = $this->createTransportMock(
            [
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_HEADER => true,
                        \CURLOPT_NOBODY => true,
                        \CURLOPT_URL => $url,
                    ],
                    self::KEY_HEADERS => [
                        'content-length' => 0,
                        'accept-ranges' => 'bytes',
                    ],
                ],
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_URL => $url,
                        \CURLOPT_FILE => $destinationPath,
                        \CURLOPT_RANGE => null,
                    ],
                    self::KEY_CODE => self::STATUS_SERVER_ERROR,
                    self::KEY_FILE_CONTENT => $partialLoadedContent,
                ],
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_URL => $url,
                        \CURLOPT_FILE => $destinationPath,
                        \CURLOPT_RANGE => null,
                    ],
                ],
            ]
        );

        $downloader = new CurlDownloader([], 2, $transport);
        $downloader->download($url, $destination);
    }

    public function testDownloadStatusError(): void
    {
        $url = 'https://test.ru/test.zip';
        $destinationPath = $this->getPathToTestFile('testDownloadStatusError.zip');
        $destination = new \SplFileInfo($destinationPath);

        $transport = $this->createTransportMock(
            [
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_HEADER => true,
                        \CURLOPT_NOBODY => true,
                        \CURLOPT_URL => $url,
                    ],
                ],
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_URL => $url,
                        \CURLOPT_FILE => $destinationPath,
                    ],
                    self::KEY_CODE => self::STATUS_SERVER_ERROR,
                ],
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_URL => $url,
                        \CURLOPT_FILE => $destinationPath,
                    ],
                    self::KEY_CODE => self::STATUS_SERVER_ERROR,
                ],
            ]
        );

        $downloader = new CurlDownloader([], 2, $transport);

        $this->expectException(DownloaderException::class);
        $this->expectExceptionMessage((string) self::STATUS_SERVER_ERROR);
        $downloader->download($url, $destination);
    }

    public function testDownloadCurlError(): void
    {
        $url = 'https://test.ru/test.zip';
        $destinationPath = $this->getPathToTestFile('testDownloadCurlError.zip');
        $destination = new \SplFileInfo($destinationPath);
        $exceptionMessage = 'test exception';

        $transport = $this->createTransportMock(
            [
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_HEADER => true,
                        \CURLOPT_NOBODY => true,
                        \CURLOPT_URL => $url,
                    ],
                ],
                [
                    self::KEY_OPTIONS => [
                        \CURLOPT_URL => $url,
                        \CURLOPT_FILE => $destinationPath,
                    ],
                    self::KEY_CURL_EXCEPTION => new \RuntimeException($exceptionMessage),
                ],
            ]
        );

        $downloader = new CurlDownloader([], 1, $transport);

        $this->expectException(DownloaderException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $downloader->download($url, $destination);
    }

    public function testDownloadBrokenUrlException(): void
    {
        $source = 'test';
        $destination = new \SplFileInfo('/test');

        $curl = new CurlDownloader();

        $this->expectException(DownloaderException::class);
        $curl->download($source, $destination);
    }

    /**
     * @param mixed[][] $requests
     */
    private function createTransportMock(array $requests = []): CurlTransport
    {
        $responses = [];
        foreach ($requests as $request) {
            $code = (int) ($request[self::KEY_CODE] ?? self::STATUS_OK);
            $headers = (array) ($request[self::KEY_HEADERS] ?? []);
            $options = (array) ($request[self::KEY_OPTIONS] ?? []);
            $httpResponse = $this->getMockBuilder(HttpResponse::class)->disableOriginalConstructor()->getMock();
            $httpResponse->method('getStatusCode')->willReturn($code);
            $httpResponse->method('getHeaders')->willReturn($headers);
            $httpResponse->method('isOk')->willReturn($code >= 200 && $code < 300);
            $responses[] = [
                self::KEY_OPTIONS => $options,
                self::KEY_RESPONSE => $httpResponse,
                self::KEY_FILE_CONTENT => $request[self::KEY_FILE_CONTENT] ?? null,
                self::KEY_CURL_EXCEPTION => $request[self::KEY_CURL_EXCEPTION] ?? null,
            ];
        }

        $getResponse = function (array $options, array $response): ?HttpResponse {
            foreach ($response[self::KEY_OPTIONS] as $name => $responseValue) {
                $optionValue = $options[(string) $name] ?? null;
                if (\is_resource($optionValue)) {
                    $metaData = stream_get_meta_data($optionValue);
                    $optionValue = $metaData['uri'];
                }
                if ($optionValue !== $responseValue) {
                    throw new \RuntimeException('Response in mock has another options');
                }
            }

            if (
                !empty($response[self::KEY_FILE_CONTENT])
                && !empty($options[\CURLOPT_FILE])
                && \is_resource($options[\CURLOPT_FILE])
            ) {
                fwrite($options[\CURLOPT_FILE], (string) $response[self::KEY_FILE_CONTENT]);
            }

            if (
                !empty($response[self::KEY_CURL_EXCEPTION])
                && $response[self::KEY_CURL_EXCEPTION] instanceof \Throwable
            ) {
                throw $response[self::KEY_CURL_EXCEPTION];
            }

            return $response[self::KEY_RESPONSE] instanceof HttpResponse ? $response[self::KEY_RESPONSE] : null;
        };

        $counter = 0;
        /** @var MockObject&CurlTransport */
        $downloader = $this->getMockBuilder(CurlTransport::class)->disableOriginalConstructor()->getMock();
        $downloader->expects($this->exactly(\count($requests)))
            ->method('run')
            ->willReturnCallback(
                function (array $options) use (&$counter, $responses, $getResponse): ?HttpResponse {
                    return $getResponse($options, $responses[(int) $counter++] ?? []);
                }
            );

        return $downloader;
    }
}
