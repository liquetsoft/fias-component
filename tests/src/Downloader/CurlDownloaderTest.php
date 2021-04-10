<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Downloader;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\Downloader\CurlDownloader;
use Liquetsoft\Fias\Component\Downloader\Downloader;
use Liquetsoft\Fias\Component\Exception\DownloaderException;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use RuntimeException;
use SplFileInfo;

/**
 * Тест для объекта, который загружает файл с помощью curl.
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
        $source = $this->createFakeData()->url;

        $destinationPath = $this->getPathToTestFile('archive.rar');
        $destination = new SplFileInfo($destinationPath);

        $curl = $this->createDownloaderMock(
            [
                true,
                200,
                null,
            ],
            function ($requestOptions) use ($source) {
                return in_array($source, $requestOptions)
                    && isset($requestOptions[CURLOPT_FILE])
                    && is_resource($requestOptions[CURLOPT_FILE])
                    && !empty($requestOptions[CURLOPT_CONNECT_ONLY])
                ;
            },
            [
                CURLOPT_CONNECT_ONLY => true,
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

        $curl = $this->createDownloaderMock();

        $this->expectException(InvalidArgumentException::class);
        $curl->download($source, $destination);
    }

    /**
     * Проверяет, что объект выбрасывает исключение, если произошла ошибка
     * во время загрузки файла.
     */
    public function testDownloadCurlErrorException(): void
    {
        $source = $this->createFakeData()->url;

        $destinationPath = $this->getPathToTestFile('archive.rar');
        $destination = new SplFileInfo($destinationPath);

        $curl = $this->createDownloaderMock(
            [
                false,
                0,
                'error',
            ]
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
        $source = $this->createFakeData()->url;

        $destinationPath = $this->getPathToTestFile('archive.rar');
        $destination = new SplFileInfo($destinationPath);

        $curl = $this->createDownloaderMock(
            [
                true,
                413,
                null,
            ]
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
        $source = $this->createFakeData()->url;

        $destinationPath = '/wrong/path/to/file.rar';
        $destination = new SplFileInfo($destinationPath);

        $curl = $this->createDownloaderMock();

        $this->expectException(DownloaderException::class);
        $curl->download($source, $destination);
    }

    /**
     * Создает настроенный мок для curl загрузчика.
     *
     * @param mixed         $return
     * @param callable|null $with
     * @param array         $additionalCurlOptions
     *
     * @return Downloader
     */
    private function createDownloaderMock(
        $return = null,
        ?callable $with = null,
        array $additionalCurlOptions = []
    ): Downloader {
        $downloader = $this->getMockBuilder(CurlDownloader::class)
            ->onlyMethods(
                [
                    'curlDownload',
                ]
            )
            ->setConstructorArgs(
                [
                    $additionalCurlOptions,
                ]
            )
            ->getMock();

        $expects = $return === null ? $this->never() : $this->once();
        $method = $downloader->expects($expects)->method('curlDownload');

        if ($with) {
            $method->with($this->callback($with));
        }

        if (is_array($return)) {
            $method->willReturn($return);
        }

        if (!($downloader instanceof Downloader)) {
            throw new RuntimeException('Wrong downloader mock.');
        }

        return $downloader;
    }
}
