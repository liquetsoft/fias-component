<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Downloader;

use Liquetsoft\Fias\Component\Exception\DownloaderException;

/**
 * Объект, который скачивает файл по ссылке с помощью curl.
 */
final class CurlDownloader implements Downloader
{
    public const DEFAULT_MAX_ATTEMPTS = 10;
    public const DEFAULT_SLEEP_BETWEEN_ATTEMPTS = 10;
    public const DEFAULT_CONNECTION_TIMEOUT = 5;
    public const DEFAULT_TIMEOUT = 60 * 25;

    private readonly array $additionalCurlOptions;

    private readonly int $maxAttempts;

    private readonly int $sleepBetweenAttempts;

    private readonly CurlTransport $transport;

    public function __construct(
        array $additionalCurlOptions = [],
        int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS,
        int $sleepBetweenAttempts = self::DEFAULT_SLEEP_BETWEEN_ATTEMPTS,
        ?CurlTransport $transport = null
    ) {
        $this->additionalCurlOptions = $additionalCurlOptions;
        $this->maxAttempts = $maxAttempts;
        $this->sleepBetweenAttempts = $sleepBetweenAttempts;
        $this->transport = $transport ?: new CurlTransport();
    }

    /**
     * {@inheritdoc}
     */
    public function download(string $url, \SplFileInfo $localFile): void
    {
        $this->checkIsUrlCorrect($url);

        $headers = $this->getHeadResponseHeaders($url);
        $contentLength = (int) ($headers['content-length'] ?? 0);
        $isRangeSupported = $contentLength > 0 && ($headers['accept-ranges'] ?? '') === 'bytes';

        $options = [
            \CURLOPT_FOLLOWLOCATION => true,
            \CURLOPT_FRESH_CONNECT => true,
            \CURLOPT_CONNECTTIMEOUT => self::DEFAULT_CONNECTION_TIMEOUT,
            \CURLOPT_TIMEOUT => self::DEFAULT_TIMEOUT,
            \CURLOPT_FILE => $this->openLocalFile($localFile, 'wb'),
        ];

        $response = new HttpResponse();
        $exception = null;
        for ($i = 0; $i < $this->maxAttempts; ++$i) {
            try {
                $exception = null;
                $response = $this->runRequest($url, $options);
            } catch (\Throwable $e) {
                $exception = $e;
            }
            if ($response->isOk()) {
                break;
            } elseif ($i < $this->maxAttempts - 1) {
                // в случае ошибки пробуем скачать файл еще раз,
                // но для этого нужно переоткрыть ресурс файла
                $this->closeLocalFile($options[\CURLOPT_FILE]);
                // следует подождать какое-то время прежде, чем возобновить попытку
                if ($this->sleepBetweenAttempts > 0) {
                    sleep($this->sleepBetweenAttempts);
                }
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
        }

        $this->closeLocalFile($options[\CURLOPT_FILE]);

        if ($exception) {
            throw new DownloaderException($exception->getMessage(), 0, $exception);
        } elseif (!$response->isOk()) {
            throw DownloaderException::create(
                "Url '%s' returned status: %s",
                $url,
                $response->getStatusCode() ?: 'no status'
            );
        }
    }

    /**
     * Открывает локальный файл, в который будет запись, и возвращает его ресурс.
     *
     * @return resource
     */
    private function openLocalFile(\SplFileInfo $localFile, string $mode)
    {
        $hLocal = @fopen($localFile->getPathname(), $mode);

        if ($hLocal === false) {
            throw DownloaderException::create(
                "Can't open local file for writing: %s",
                $localFile->getPathname()
            );
        } elseif (!flock($hLocal, \LOCK_EX)) {
            throw DownloaderException::create(
                'Unable to obtain lock for file: %s',
                $localFile->getPathname()
            );
        }

        return $hLocal;
    }

    /**
     * Правильно закрывает ресурс локального файла.
     *
     * @param resource $hLocal
     */
    private function closeLocalFile($hLocal): void
    {
        flock($hLocal, \LOCK_UN);
        fclose($hLocal);
    }

    /**
     * Возвращает список заголовков из ответа на HEAD запрос.
     *
     * @return array<string, string>
     */
    private function getHeadResponseHeaders(string $url): array
    {
        $options = [
            \CURLOPT_HEADER => true,
            \CURLOPT_NOBODY => true,
            \CURLOPT_RETURNTRANSFER => true,
        ];

        return $this->runRequest($url, $options)->getHeaders();
    }

    /**
     * Отправляет запрос с помощью curl.
     *
     * @throws DownloaderException
     */
    private function runRequest(string $url, array $options): HttpResponse
    {
        $fullOptionsList = $this->additionalCurlOptions + $options;
        $fullOptionsList[\CURLOPT_URL] = $url;

        return $this->transport->run($fullOptionsList);
    }

    /**
     * Выбрасывает исключение, если ссылка указана неверно.
     */
    private function checkIsUrlCorrect(string $url): void
    {
        if (!preg_match('#https?://.+\.[^.]+.*#', $url)) {
            throw DownloaderException::create('Wrong url format: %s', $url);
        }
    }
}
