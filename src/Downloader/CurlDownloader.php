<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Downloader;

use Liquetsoft\Fias\Component\Exception\DownloaderException;

/**
 * Объект, который скачивает файл по ссылке с помощью curl.
 */
class CurlDownloader implements Downloader
{
    private array $additionalCurlOptions;

    private int $maxAttempts;

    /**
     * @param array $additionalCurlOptions
     * @param int   $maxAttempts
     */
    public function __construct(array $additionalCurlOptions = [], int $maxAttempts = 10)
    {
        $this->additionalCurlOptions = $additionalCurlOptions;
        $this->maxAttempts = $maxAttempts;
    }

    /**
     * {@inheritdoc}
     */
    public function download(string $url, \SplFileInfo $localFile): void
    {
        if (!preg_match('#https?://.+\.[^.]+.*#', $url)) {
            throw new \InvalidArgumentException("Wrong url format: {$url}");
        }

        $headers = $this->getHeadResponseHeaders($url);
        $contentLength = (int) ($headers['content-length'] ?? 0);
        $isRangeSupported = $contentLength > 0 && ($headers['accept-ranges'] ?? '') === 'bytes';

        $options = [
            \CURLOPT_FOLLOWLOCATION => true,
            \CURLOPT_FRESH_CONNECT => true,
            \CURLOPT_CONNECTTIMEOUT => 5,
            \CURLOPT_TIMEOUT => 60 * 25,
            \CURLOPT_FILE => $this->openLocalFile($localFile, 'wb'),
        ];

        $response = new CurlDownloaderResponse();
        for ($i = 0; $i < $this->maxAttempts; ++$i) {
            $response = $this->runRequest($url, $options);
            if ($response->isOk() && !$response->hasError()) {
                break;
            }
            // в случае ошибки пробуем скачать файл еще раз,
            // но для этого нужно переоткрыть ресурс файла
            fclose($options[\CURLOPT_FILE]);
            // если уже скачали какие-то данные и сервер поддерживает Range,
            // пробуем продолжить с того же места
            clearstatcache(true, $localFile->getRealPath());
            $fileSize = (int) filesize($localFile->getRealPath());
            if ($fileSize > 0 && $isRangeSupported) {
                $options[\CURLOPT_FILE] = $this->openLocalFile($localFile, 'ab');
                $options[\CURLOPT_RANGE] = $fileSize . '-' . ($contentLength - 1);
            } else {
                $options[\CURLOPT_FILE] = $this->openLocalFile($localFile, 'wb');
            }
        }

        fclose($options[\CURLOPT_FILE]);

        if ($response->hasError()) {
            $message = sprintf(
                "There was an error while downloading '%s': %s.",
                $url,
                (string) $response->getError()
            );
            throw new DownloaderException($message);
        }

        if (!$response->isOk()) {
            $status = 'xxx';
            if ($response->getStatusCode() !== 0) {
                $status = $response->getStatusCode();
            }
            $message = sprintf(
                "Url '%s' returned status: %s.",
                $url,
                $status
            );
            throw new DownloaderException($message);
        }
    }

    /**
     * Возвращает список заголовков из ответа на HEAD запрос.
     *
     * @param string $url
     *
     * @return array<string, string>
     */
    private function getHeadResponseHeaders(string $url): array
    {
        $response = $this->runRequest(
            $url,
            [
                \CURLOPT_HEADER => true,
                \CURLOPT_NOBODY => true,
                \CURLOPT_RETURNTRANSFER => true,
            ]
        );

        return $response->getHeaders();
    }

    /**
     * Открывает локальный файл, в который будет вестись запись,
     * и возвращает его ресурс.
     *
     * @param \SplFileInfo $localFile
     * @param string       $mode
     *
     * @return resource
     */
    private function openLocalFile(\SplFileInfo $localFile, string $mode)
    {
        $hLocal = @fopen($localFile->getPathname(), $mode);

        if ($hLocal === false) {
            $message = sprintf(
                "Can't open local file for writing: %s.",
                $localFile->getPathname()
            );
            throw new DownloaderException($message);
        }

        return $hLocal;
    }

    /**
     * Отправляет запрос с помощью curl и возвращает содержимое, статус ответа и список заголовков.
     *
     * @param string $url
     * @param array  $options
     *
     * @return CurlDownloaderResponse
     */
    protected function runRequest(string $url, array $options): CurlDownloaderResponse
    {
        $fullOptionsList = $this->additionalCurlOptions + $options;
        $fullOptionsList[\CURLOPT_URL] = $url;

        return new CurlDownloaderResponse($this->runCurlRequest($fullOptionsList));
    }

    /**
     * Отправляет запрос с помощью curl и возвращает содержимое, статус ответа и список заголовков.
     *
     * @param array $options
     *
     * @return array
     */
    protected function runCurlRequest(array $options): array
    {
        $ch = curl_init();
        if ($ch === false) {
            throw new DownloaderException("Can't init curl resource.");
        }

        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $response = [
            (int) curl_getinfo($ch, \CURLINFO_HTTP_CODE),
            $content,
            curl_error($ch),
        ];
        curl_close($ch);

        return $response;
    }
}
