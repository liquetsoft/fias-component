<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\HttpTransport;

/**
 * Фабрика, которая создает объекты с http ответами.
 */
final class HttpResponseFactory
{
    private function __construct()
    {
    }

    /**
     * Создает объект ответа из заданных составных частей.
     */
    public static function create(int $statusCode, array $headers = [], string $payload = ''): HttpResponse
    {
        return new BaseHttpResponse($statusCode, $headers, $payload);
    }

    /**
     * Создает объект, пытаясь распарсить сырой текст ответа.
     */
    public static function createFromText(string $response): HttpResponse
    {
        $statusCode = self::extractStatusCodeFromResponse($response);
        $headers = self::extractHeadersFromResponse($response);
        $payload = self::extractPayloadFromResponse($response);

        return new BaseHttpResponse($statusCode, $headers, $payload);
    }

    /**
     * Извлекает код статуса из текста сырого ответа.
     */
    private static function extractStatusCodeFromResponse(string $response): int
    {
        $response = ltrim($response);
        if (preg_match('#^HTTP/\S+\s+([0-9]{3})#i', $response, $matches) === 1) {
            return (int) $matches[1];
        }

        return 0;
    }

    /**
     * Извлекает заголовки из текста сырого ответа.
     */
    private static function extractHeadersFromResponse(string $response): array
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
    private static function extractPayloadFromResponse(string $response): string
    {
        $explodedResponse = explode("\r\n\r\n", $response, 2);

        return $explodedResponse[1] ?? '';
    }
}
