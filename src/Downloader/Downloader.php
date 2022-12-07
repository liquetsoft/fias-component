<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Downloader;

use Liquetsoft\Fias\Component\Exception\DownloaderException;

/**
 * Интерфейс для объекта, который скачивает файл по ссылке.
 */
interface Downloader
{
    /**
     * Скачивает файл по ссылке из первого параметра в локальный файл,
     * указанный во втором параметре.
     *
     * @param string       $url
     * @param \SplFileInfo $localFile
     *
     * @throws \InvalidArgumentException
     * @throws DownloaderException
     */
    public function download(string $url, \SplFileInfo $localFile): void;
}
