<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\DownloaderTest;

use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Downloader\CurlDownloader;
use Liquetsoft\Fias\Component\Exception\DownloaderException;
use SplFileInfo;
use InvalidArgumentException;

/**
 * Тест для объекта, который загружает файл с помощью curl.
 */
class CurlDownloaderTest extends BaseCase
{
    /**
     * Проверяет, что объект загружает файл.
     */
    public function testDownload()
    {
        $source = $this->createFakeData()->url;

        $destinationPath = $this->getPathToTestFile('archive.rar');
        $destination = new SplFileInfo($destinationPath);

        $curl = $this->getMockBuilder(CurlDownloader::class)
            ->onlyMethods(['curlDownload'])
            ->disableOriginalConstructor()
            ->getMock();
        $curl->expects($this->once())->method('curlDownload')
            ->with($this->callback(function ($requestOptions) use ($source) {
                return in_array($source, $requestOptions)
                    && isset($requestOptions[CURLOPT_FILE])
                    && is_resource($requestOptions[CURLOPT_FILE]);
            }))
            ->will($this->returnValue([true, 200, null]));

        $curl->download($source, $destination);
    }

    /**
     * Проверяет, что объект выбрасывает исключение, если ссылка задана неверно.
     */
    public function testDownloadBrokenUrlException()
    {
        $source = 'test';

        $destinationPath = $this->getPathToTestFile('archive.rar');
        $destination = new SplFileInfo($destinationPath);

        $curl = $this->getMockBuilder(CurlDownloader::class)
            ->onlyMethods(['curlDownload'])
            ->disableOriginalConstructor()
            ->getMock();
        $curl->expects($this->never())->method('curlDownload');

        $this->expectException(InvalidArgumentException::class);
        $curl->download($source, $destination);
    }

    /**
     * Проверяет, что объект выбрасывает исключение, если произошла ошибка
     * во время загрузки файла.
     */
    public function testDownloadCurlErrorException()
    {
        $source = $this->createFakeData()->url;

        $destinationPath = $this->getPathToTestFile('archive.rar');
        $destination = new SplFileInfo($destinationPath);

        $curl = $this->getMockBuilder(CurlDownloader::class)
            ->onlyMethods(['curlDownload'])
            ->disableOriginalConstructor()
            ->getMock();
        $curl->expects($this->once())->method('curlDownload')
            ->will($this->returnValue([false, 0, 'error']));

        $this->expectException(DownloaderException::class);
        $curl->download($source, $destination);
    }

    /**
     * Проверяет, что объект выбрасывает исключение, если в ответ по ссылке возвращается
     * любой статус кроме 200.
     */
    public function testDownloadWrongResponseCodeException()
    {
        $source = $this->createFakeData()->url;

        $destinationPath = $this->getPathToTestFile('archive.rar');
        $destination = new SplFileInfo($destinationPath);

        $curl = $this->getMockBuilder(CurlDownloader::class)
            ->onlyMethods(['curlDownload'])
            ->disableOriginalConstructor()
            ->getMock();
        $curl->expects($this->once())->method('curlDownload')
            ->will($this->returnValue([true, 413, null]));

        $this->expectException(DownloaderException::class);
        $curl->download($source, $destination);
    }

    /**
     * Проверяет, что объект выбрасывает исключение, если не удалось открыть
     * целевой файл для записи в локальную файловую систему.
     */
    public function testDownloadCantOpenFileException()
    {
        $source = $this->createFakeData()->url;

        $destinationPath = '/wrong/path/to/file.rar';
        $destination = new SplFileInfo($destinationPath);

        $curl = $this->getMockBuilder(CurlDownloader::class)
            ->onlyMethods(['curlDownload'])
            ->disableOriginalConstructor()
            ->getMock();
        $curl->expects($this->never())->method('curlDownload');

        $this->expectException(DownloaderException::class);
        $curl->download($source, $destination);
    }
}
