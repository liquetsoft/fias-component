<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\HttpTransport;

use Liquetsoft\Fias\Component\Exception\HttpTransportException;

/**
 * Объект, который может отправлять запросы с помощью curl.
 */
final class CurlHttpTransport implements HttpTransport
{
    public const DEFAULT_CONNECTION_TIMEOUT = 5;
    public const DEFAULT_TIMEOUT = 60 * 25;

    private readonly array $defaultOptions;

    public function __construct(array $defaultOptions = [])
    {
        $preDefinedOptions = [
            \CURLOPT_CONNECTTIMEOUT => self::DEFAULT_CONNECTION_TIMEOUT,
            \CURLOPT_TIMEOUT => self::DEFAULT_TIMEOUT,
        ];
        $this->defaultOptions = $preDefinedOptions + $defaultOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function head(string $url): HttpResponse
    {
        return $this->run(
            $url,
            [
                \CURLOPT_HEADER => true,
                \CURLOPT_NOBODY => true,
                \CURLOPT_RETURNTRANSFER => true,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $url, array $params = []): HttpResponse
    {
        return $this->run(
            $url,
            [
                \CURLOPT_HEADER => true,
                \CURLOPT_NOBODY => true,
                \CURLOPT_RETURNTRANSFER => true,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function download(string $url, $destination, ?int $bytesFrom = null, ?int $bytesTo = null): HttpResponse
    {
        $options = [
            \CURLOPT_FOLLOWLOCATION => true,
            \CURLOPT_FRESH_CONNECT => true,
            \CURLOPT_FILE => $destination,
        ];
        if ($bytesFrom !== null && $bytesTo !== null) {
            $options[\CURLOPT_RANGE] = $bytesFrom . '-' . ($bytesTo - 1);
        }

        return $this->run($url, $options);
    }

    /**
     * Отправляет запрос с помощью curl и возвращает содержимое в виде объекта HttpResponse.
     */
    public function run(string $url, array $options): HttpResponse
    {
        $ch = curl_init();
        if ($ch === false) {
            throw HttpTransportException::create("Can't init curl resource");
        }

        $options = $this->defaultOptions + $options;
        $options[\CURLOPT_URL] = $url;
        curl_setopt_array($ch, $options);

        $content = curl_exec($ch);
        $error = curl_error($ch);
        $code = curl_getinfo($ch, \CURLINFO_RESPONSE_CODE);

        curl_close($ch);

        if (!empty($error)) {
            throw HttpTransportException::create("Error while querying '%s': %s", $url, $error);
        }

        if (!empty($content) && \is_string($content)) {
            return HttpResponseFactory::createFromText($content);
        }

        return HttpResponseFactory::create((int) $code);
    }
}
