<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\HttpTransport;

use Liquetsoft\Fias\Component\Exception\HttpTransportException;

/**
 * Объект, который может отправлять запросы с помощью curl.
 */
final class HttpTransportCurl implements HttpTransport
{
    private const DEFAULT_CONNECTION_TIMEOUT = 5;
    private const DEFAULT_TIMEOUT = 60 * 25;

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
    public function head(string $url): HttpTransportResponse
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
    public function get(string $url, array $params = []): HttpTransportResponse
    {
        return $this->run(
            $url,
            [
                \CURLOPT_HEADER => true,
                \CURLOPT_RETURNTRANSFER => true,
                \CURLOPT_POSTFIELDS => $params,
                \CURLOPT_FOLLOWLOCATION => true,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function download(string $url, $destination, ?int $bytesFrom = null, ?int $bytesTo = null): HttpTransportResponse
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
    public function run(string $url, array $options): HttpTransportResponse
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
        $responseCode = (int) curl_getinfo($ch, \CURLINFO_RESPONSE_CODE);

        curl_close($ch);

        if (!empty($error)) {
            throw HttpTransportException::create("Error while querying '%s': %s", $url, $error);
        }

        $headers = [];
        $payload = '';
        $payloadJson = null;
        if (\is_string($content) && $content !== '') {
            $headers = $this->extractHeadersFromResponse($content);
            $payload = $this->extractPayloadFromResponse($content);
            $contentType = $this->getHeaderValue('content-type', $headers);
            if ($contentType !== null && str_contains($contentType, 'json')) {
                $payloadJson = $this->decodeJsonPayload($payload);
            }
        }

        return HttpTransportResponseFactory::create(
            $responseCode,
            $headers,
            $payload,
            $payloadJson
        );
    }

    /**
     * Извлекает заголовки из текста сырого ответа.
     *
     * @return array<string, string>
     */
    private function extractHeadersFromResponse(string $response): array
    {
        $explodedResponse = explode("\r\n\r\n", $response);

        $headers = [];
        $rawHeaders = explode("\n", $explodedResponse[0]);
        foreach ($rawHeaders as $rawHeader) {
            $rawHeaderExplode = explode(':', $rawHeader, 2);
            if (\count($rawHeaderExplode) < 2) {
                continue;
            }
            $headers[$rawHeaderExplode[0]] = $rawHeaderExplode[1];
        }

        return $headers;
    }

    /**
     * Извлекает тело ответа из текста.
     */
    private function extractPayloadFromResponse(string $response): string
    {
        $explodedResponse = explode("\r\n\r\n", $response, 2);

        return $explodedResponse[1] ?? '';
    }

    /**
     * Ищет указанный HTTP заголовок в массиве заголовков.
     *
     * @param array<string, string> $headers
     */
    private function getHeaderValue(string $header, array $headers): ?string
    {
        $trimmedHeader = str_replace('_', '-', strtolower(trim($header)));

        foreach ($headers as $headerName => $headerValue) {
            if ($trimmedHeader === str_replace('_', '-', strtolower(trim($headerName)))) {
                return $headerValue;
            }
        }

        return null;
    }

    /**
     * Конвертирует json ответ в объект.
     */
    private function decodeJsonPayload(string $payload): mixed
    {
        try {
            return json_decode($payload, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            throw HttpTransportException::wrap($e);
        }
    }
}
