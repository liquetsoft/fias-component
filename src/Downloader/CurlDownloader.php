<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Downloader;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\Exception\DownloaderException;
use SplFileInfo;

/**
 * Объект, который скачивает файл по ссылке с помощью curl.
 */
class CurlDownloader implements Downloader
{
    /**
     * @var array
     */
    private $additionalCurlOptions;

    /**
     * @param array $additionalCurlOptions
     */
    public function __construct(array $additionalCurlOptions = [])
    {
        $this->additionalCurlOptions = $additionalCurlOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function download(string $url, SplFileInfo $localFile): void
    {
        if (!preg_match('#https?://.+\.[^.]+.*#', $url)) {
            throw new InvalidArgumentException("Wrong url format: {$url}");
        }

        $fh = $this->openLocalFile($localFile);
        $requestOptions = $this->createRequestOptions($url, $fh);

        [$res, $httpCode, $error] = $this->curlDownload($requestOptions);
        fclose($fh);

        if ($res === false) {
            throw new DownloaderException("Error while downloading '{$url}': {$error}");
        } elseif ($httpCode !== 200) {
            throw new DownloaderException("Url '{$url}' returns status: {$httpCode}");
        }
    }

    /**
     * Загружает файл по ссылке в указанный файл.
     *
     * @param array $requestOptions
     *
     * @return array
     *
     * @throws DownloaderException
     */
    protected function curlDownload(array $requestOptions): array
    {
        $ch = curl_init();
        if ($ch === false) {
            throw new DownloaderException("Can't init curl resource.");
        }

        curl_setopt_array($ch, $requestOptions);

        $res = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [$res, $httpCode, $error];
    }

    /**
     * Открывает локальный файл, в который будет вестись запись и возвращает его
     * ресурс.
     *
     * @param SplFileInfo $localFile
     *
     * @return resource
     *
     * @throws DownloaderException
     */
    protected function openLocalFile(SplFileInfo $localFile)
    {
        $hLocal = @fopen($localFile->getPathname(), 'wb');

        if ($hLocal === false) {
            throw new DownloaderException(
                "Can't open local file for writing: " . $localFile->getPathname()
            );
        }

        return $hLocal;
    }

    /**
     * Создаем массив настроек для запроса.
     *
     * @param string   $url
     * @param resource $fh
     *
     * @return array
     */
    protected function createRequestOptions(string $url, $fh): array
    {
        $requestOptions = $this->additionalCurlOptions ?: [];

        $requestOptions[CURLOPT_URL] = $url;
        $requestOptions[CURLOPT_FILE] = $fh;
        $requestOptions[CURLOPT_FOLLOWLOCATION] = true;
        $requestOptions[CURLOPT_FRESH_CONNECT] = true;

        return $requestOptions;
    }
}
