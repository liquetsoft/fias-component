<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Downloader;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\Downloader\CurlDownloader;
use Liquetsoft\Fias\Component\Downloader\Downloader;
use Liquetsoft\Fias\Component\Exception\DownloaderException;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;
use SplFileInfo;

/**
 * Тест для объекта, который загружает файл с помощью curl.
 *
 * @internal
 */
class CurlDownloaderTest extends BaseCase
{
    /**
     * Проверяет, что объект загружает файл.
     *
     * @throws DownloaderException
     */
    public function testDownload(): void
    {
        $source = $this->createFakeData()->url();

        $destinationPath = $this->getPathToTestFile('archive.rar');
        $destination = new SplFileInfo($destinationPath);

        $curl = $this->createDownloaderMock(
            function (array $options) use ($source) {
                if (
                    !empty($options[\CURLOPT_HEADER])
                    || ($options[\CURLOPT_URL] === $source
                        && \is_resource($options[\CURLOPT_FILE])
                        && !empty($options[\CURLOPT_CONNECT_ONLY]))
                ) {
                    return [200, '', null];
                }

                return [500, '', 'error'];
            },
            [
                \CURLOPT_CONNECT_ONLY => true,
            ]
        );

        $curl->download($source, $destination);
    }

    /**
     * Проверяет, что объект выбрасывает исключение, если ссылка задана неверно.
     *
     * @throws DownloaderException
     */
    public function testDownloadBrokenUrlException(): void
    {
        $source = 'test';

        $destinationPath = $this->getPathToTestFile('archive.rar');
        $destination = new SplFileInfo($destinationPath);

        $curl = new CurlDownloader();

        $this->expectException(InvalidArgumentException::class);
        $curl->download($source, $destination);
    }

    /**
     * Проверяет, что объект выбрасывает исключение, если произошла ошибка
     * во время загрузки файла.
     */
    public function testDownloadCurlErrorException(): void
    {
        $source = $this->createFakeData()->url();

        $destinationPath = $this->getPathToTestFile('archive.rar');
        $destination = new SplFileInfo($destinationPath);

        $curl = $this->createDownloaderMock(
            function (array $options) {
                if (!empty($options[\CURLOPT_HEADER])) {
                    return [200, '', null];
                }

                return [200, false, 'error'];
            },
        );

        $this->expectException(DownloaderException::class);
        $curl->download($source, $destination);
    }

    /**
     * Проверяет, что объект выбрасывает исключение, если в ответ по ссылке возвращается
     * любой статус кроме 200.
     */
    public function testDownloadWrongResponseCodeException(): void
    {
        $source = $this->createFakeData()->url();

        $destinationPath = $this->getPathToTestFile('archive.rar');
        $destination = new SplFileInfo($destinationPath);

        $curl = $this->createDownloaderMock(
            function (array $options) {
                if (!empty($options[\CURLOPT_HEADER])) {
                    return [200, '', null];
                }

                return [500, '', null];
            },
        );

        $this->expectException(DownloaderException::class);
        $curl->download($source, $destination);
    }

    /**
     * Проверяет, что объект выбрасывает исключение, если не удалось открыть
     * целевой файл для записи в локальную файловую систему.
     */
    public function testDownloadCantOpenFileException(): void
    {
        $source = $this->createFakeData()->url();

        $destinationPath = '/wrong/path/to/file.rar';
        $destination = new SplFileInfo($destinationPath);

        $curl = $this->createDownloaderMock(
            function (array $options) {
                return [200, '', null];
            }
        );

        $this->expectException(DownloaderException::class);
        $curl->download($source, $destination);
    }

    /**
     * Создает настроенный мок для curl загрузчика.
     *
     * @param callable $with
     * @param array    $additionalCurlOptions
     *
     * @return Downloader
     */
    private function createDownloaderMock(callable $with, array $additionalCurlOptions = []): Downloader
    {
        /** @var MockObject&Downloader */
        $downloader = $this->getMockBuilder(CurlDownloader::class)
            ->onlyMethods(
                [
                    'runCurlRequest',
                ]
            )
            ->setConstructorArgs(
                [
                    $additionalCurlOptions,
                ]
            )
            ->getMock();

        $downloader->expects($this->atLeastOnce())
            ->method('runCurlRequest')
            ->willReturnCallback($with);

        return $downloader;
    }
}
