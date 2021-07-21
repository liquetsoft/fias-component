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
     * @var int
     */
    private $maxAttempts;

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
    public function download(string $url, SplFileInfo $localFile): void
    {
        if (!preg_match('#https?://.+\.[^.]+.*#', $url)) {
            throw new InvalidArgumentException("Wrong url format: {$url}");
        }

        $headers = $this->getHeadResponseHeaders($url);
        $contentLength = (int) ($headers['content-length'] ?? 0);
        $isRangeSupported = $contentLength > 0 && ($headers['accept-ranges'] ?? '') === 'bytes';

        $options = [
            \CURLOPT_FOLLOWLOCATION => true,
            \CURLOPT_FRESH_CONNECT => true,
            \CURLOPT_CONNECTTIMEOUT => 5,
            \CURLOPT_TIMEOUT => 60 * 15,
            \CURLOPT_FILE => $this->openLocalFile($localFile, 'wb'),
        ];

        for ($i = 0; $i < $this->maxAttempts; ++$i) {
            $response = $this->runCurlRequest($url, $options);
            if ($response['isOk'] && empty($response['error'])) {
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

        if (!empty($response['error'])) {
            $message = sprintf(
                "There was an error while downloading '%s': %s.",
                $url,
                $response['error']
            );
            throw new DownloaderException($message);
        }

        if (!$response['isOk']) {
            $message = sprintf(
                "Url '%s' returned status: %s.",
                $url,
                $response['status']
            );
            throw new DownloaderException($message);
        }
    }

    /**
     * Возвращает список заголовков из ответа на HEAD запрос.
     *
     * @param string $url
     *
     * @return array
     */
    private function getHeadResponseHeaders(string $url): array
    {
        $response = $this->runCurlRequest(
            $url,
            [
                \CURLOPT_HEADER => true,
                \CURLOPT_NOBODY => true,
                \CURLOPT_RETURNTRANSFER => true,
            ]
        );

        return $response['headers'];
    }

    /**
     * Открывает локальный файл, в который будет вестись запись,
     * и возвращает его ресурс.
     *
     * @param SplFileInfo $localFile
     * @param string      $mode
     *
     * @return resource
     */
    private function openLocalFile(SplFileInfo $localFile, string $mode)
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
     * @return array
     */
    private function runCurlRequest(string $url, array $options): array
    {
        $fullOptionsList = $this->additionalCurlOptions + $options;
        $fullOptionsList[\CURLOPT_URL] = $url;

        $ch = curl_init();
        if ($ch === false) {
            throw new DownloaderException("Can't init curl resource.");
        }

        curl_setopt_array($ch, $fullOptionsList);
        $content = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        $response = [
            'status' => $statusCode,
            'isOk' => $statusCode >= 200 && $statusCode < 300,
            'headers' => $this->extractHeadersFromContent($content),
            'error' => curl_error($ch),
        ];
        curl_close($ch);

        return $response;
    }

    /**
     * Получает список заголовков из http ответа.
     *
     * @param mixed $content
     *
     * @return array<string, string>
     */
    private function extractHeadersFromContent(mixed $content): array
    {
        if (!\is_string($content)) {
            return [];
        }

        $explodeHeadersContent = explode("\n\n", $content, 2);

        $headers = [];
        $rawHeaders = explode("\n", $explodeHeadersContent[0]);
        foreach ($rawHeaders as $rawHeader) {
            $rawHeaderExplode = explode(':', $rawHeader, 2);
            if (\count($rawHeaderExplode) < 2) {
                continue;
            }
            $name = str_replace('_', '-', strtolower(trim($rawHeaderExplode[0])));
            $value = strtolower(trim($rawHeaderExplode[1]));
            $headers[$name] = $value;
        }

        return $headers;
    }
}
