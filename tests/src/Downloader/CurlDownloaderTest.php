<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Downloader;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\Downloader\CurlDownloader;
use Liquetsoft\Fias\Component\Downloader\Downloader;
use Liquetsoft\Fias\Component\Exception\DownloaderException;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use SplFileInfo;

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

        $curl = $this->createBaseCurlMock();
        $curl->expects($this->once())
            ->method('curlDownload')
            ->with(
                $this->callback(
                    function ($requestOptions) use ($source) {
                        return in_array($source, $requestOptions)
                            && isset($requestOptions[CURLOPT_FILE])
                            && is_resource($requestOptions[CURLOPT_FILE]);
                    }
                )
            )
            ->willReturn(
                [
                    true,
                    200,
                    null,
                ]
            );

        $curl->download($source, $destination);
    }

    /**
     * Проверяет, что объект выбрасывает исключение, если ссылка задана неверно.
     *
     * @throws DownloaderException
     */
    public function testDownloadBrokenUrlException()
    {
        $source = 'test';

        $destinationPath = $this->getPathToTestFile('archive.rar');
        $destination = new SplFileInfo($destinationPath);

        $curl = $this->createCurlMockWithReturn();

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

        $curl = $this->createCurlMockWithReturn(
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
    public function testDownloadWrongResponseCodeException()
    {
        $source = $this->createFakeData()->url;

        $destinationPath = $this->getPathToTestFile('archive.rar');
        $destination = new SplFileInfo($destinationPath);

        $curl = $this->createCurlMockWithReturn(
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
    public function testDownloadCantOpenFileException()
    {
        $source = $this->createFakeData()->url;

        $destinationPath = '/wrong/path/to/file.rar';
        $destination = new SplFileInfo($destinationPath);

        $curl = $this->createCurlMockWithReturn();

        $this->expectException(DownloaderException::class);
        $curl->download($source, $destination);
    }

    /**
     * Создает настроенный мок для curl загрузчика.
     *
     * @param mixed $return
     *
     * @return Downloader
     */
    private function createCurlMockWithReturn($return = null): Downloader
    {
        $curl = $this->createBaseCurlMock();

        if ($return === false) {
            $curl->expects($this->never())->method('curlDownload');
        } elseif (is_array($return)) {
            $curl->expects($this->once())
                ->method('curlDownload')
                ->willReturn($return);
        }

        if (!($curl instanceof Downloader)) {
            throw new RuntimeException('Wrong downloader mock.');
        }

        return $curl;
    }

    /**
     * Создает базовый мок для загрузчика.
     *
     * @return MockObject
     */
    private function createBaseCurlMock(): MockObject
    {
        return $this->getMockBuilder(CurlDownloader::class)
            ->onlyMethods(
                [
                    'curlDownload',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
    }
}
