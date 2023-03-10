<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Downloader;

use Liquetsoft\Fias\Component\Exception\DownloaderException;
use Liquetsoft\Fias\Component\HttpTransport\HttpTransport;

/**
 * Встроенная реализация загрузчика.
 */
final class DownloaderImpl implements Downloader
{
    private const DEFAULT_MAX_ATTEMPTS = 10;
    private const FILE_MODE_NEW = 'wb';
    private const FILE_MODE_ADD = 'ab';

    private readonly HttpTransport $transport;

    private readonly int $maxAttempts;

    public function __construct(
        HttpTransport $transport,
        int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS
    ) {
        $this->transport = $transport;
        $this->maxAttempts = $maxAttempts;
    }

    /**
     * {@inheritdoc}
     */
    public function download(string $url, \SplFileInfo $localFile): void
    {
        try {
            $headResponse = $this->transport->head($url);
        } catch (\Throwable $e) {
            throw DownloaderException::wrap($e);
        }

        $fileHandler = $this->openLocalFile($localFile, self::FILE_MODE_NEW);
        for ($try = 1; $try <= $this->maxAttempts; $try++) {
            try {
                $response = $this->transport->download($url, $fileHandler, $bytesFrom ?? null, $bytesTo ?? null);
                if ($response->isOk()) {
                    $this->closeLocalFile($fileHandler);
                    break;
                } else {
                    throw DownloaderException::create("Url '%s' returned status: %s", $url, $response->getStatusCode());
                }
            } catch (\Throwable $e) {
                $this->closeLocalFile($fileHandler);
                if ($try === $this->maxAttempts) {
                    throw DownloaderException::wrap($e);
                }
            }
            // php запоминает описания файлов, поэтому чтобы получить
            // реальный размер, нужно очистить кэш
            clearstatcache(true, $localFile->getRealPath());
            // если уже скачали какие-то данные и сервер поддерживает Range,
            // пробуем продолжить с того же места
            $fileSize = filesize($localFile->getRealPath());
            if (!empty($fileSize) && $headResponse->isRangeSupported()) {
                $fileHandler = $this->openLocalFile($localFile, self::FILE_MODE_ADD);
                $bytesFrom = $fileSize;
                $bytesTo = $headResponse->getContentLength() - 1;
            } else {
                $fileHandler = $this->openLocalFile($localFile, self::FILE_MODE_NEW);
            }
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
            throw DownloaderException::create("Can't open local file for writing: %s", $localFile->getPathname());
        }

        if (!flock($hLocal, \LOCK_EX)) {
            throw DownloaderException::create('Unable to obtain lock for file: %s', $localFile->getPathname());
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
}