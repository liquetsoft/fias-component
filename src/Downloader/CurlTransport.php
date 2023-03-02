<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Downloader;

use Liquetsoft\Fias\Component\Exception\DownloaderException;

/**
 * Объект, который позволяет отправлять запросы с помощью curl.
 *
 * @internal
 */
class CurlTransport
{
    /**
     * Отправляет запрос с помощью curl и возвращает содержимое в виде объекта HttpResponse.
     */
    public function run(array $options): HttpResponse
    {
        $ch = curl_init();
        if ($ch === false) {
            throw DownloaderException::create("Can't init curl resource");
        }

        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error !== '') {
            throw DownloaderException::create(
                "There was an error while downloading '%s': %s",
                $options[\CURLOPT_URL] ?? '',
                $error
            );
        }

        return new HttpResponse($content ?: '');
    }
}
