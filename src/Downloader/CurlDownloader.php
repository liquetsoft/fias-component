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
    public const DEFAULT_CONNECTION_TIMEOUT = 5;
    public const DEFAULT_TIMEOUT = 60 * 25;

    private readonly array $additionalCurlOptions;

    private readonly int $maxAttempts;

    private readonly CurlTransport $transport;

    public function __construct(
        array $additionalCurlOptions = [],
        int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS,
        ?CurlTransport $transport = null
    ) {
        $this->additionalCurlOptions = $additionalCurlOptions;
        $this->maxAttempts = $maxAttempts;
        $this->transport = $transport ?: new CurlTransport();
    }

    /**
     * {@inheritdoc}
     */
    public function download(string $url, \SplFileInfo $localFile): void
    {
        $this->checkUrl($url);

        $headResponse = $this->getHeadResponse($url);

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
            }
            // в случае ошибки пробуем скачать файл еще раз,
            // но для этого нужно переоткрыть ресурс файла
            $this->closeLocalFile($options[\CURLOPT_FILE]);
            // php запоминает описания файлов, поэтому чтобы получить
            // реальный размер, нужно очистить кэш
            clearstatcache(true, $localFile->getRealPath());
            // если уже скачали какие-то данные и сервер поддерживает Range,
            // пробуем продолжить с того же места
            $fileSize = filesize($localFile->getRealPath());
            if (!empty($fileSize) && $headResponse->isRangeSupported()) {
                $options[\CURLOPT_FILE] = $this->openLocalFile($localFile, 'ab');
                $options[\CURLOPT_RANGE] = $fileSize . '-' . ($headResponse->getContentLength() - 1);
            } else {
                $options[\CURLOPT_FILE] = $this->openLocalFile($localFile, 'wb');
            }
        }

        $this->closeLocalFile($options[\CURLOPT_FILE]);

        if ($exception) {
            throw DownloaderException::wrap($exception);
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

        if (empty($hLocal)) {
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
     */
    private function getHeadResponse(string $url): HttpResponse
    {
        return $this->runRequest(
            $url,
            [
                \CURLOPT_HEADER => true,
                \CURLOPT_NOBODY => true,
                \CURLOPT_RETURNTRANSFER => true,
            ]
        );
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
     * Проверяет, что url задан в правильном формате.
     */
    private function checkUrl(string $url): void
    {
        if (!preg_match('#https?://.+\.[^.]+.*#', $url)) {
            throw DownloaderException::create('Wrong url format: %s', $url);
        }
    }
}
