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
     * @inheritdoc
     */
    public function download(string $url, SplFileInfo $localFile): void
    {
        if (!preg_match('#https?://.+\.[^.]+.*#', $url)) {
            throw new InvalidArgumentException("Wrong url format: {$url}");
        }

        $fh = $this->openLocalFile($localFile);
        $requestOptions = [
            CURLOPT_URL => $url,
            CURLOPT_FILE => $fh,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_FRESH_CONNECT => true,
        ];

        list($res, $httpCode, $error) = $this->curlDownload($requestOptions);
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
        if (!is_resource($ch)) {
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
}
