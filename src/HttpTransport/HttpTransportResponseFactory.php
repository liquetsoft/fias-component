<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\HttpTransport;

/**
 * Фабрика, которая создает объекты с http ответами.
 */
final class HttpTransportResponseFactory
{
    private function __construct()
    {
    }

    /**
     * Создает объект ответа из заданных составных частей.
     */
    public static function create(int $statusCode, array $headers = [], string $payload = '', mixed $payloadJson = null): HttpTransportResponse
    {
        return new HttpTransportResponseImpl(
            $statusCode,
            $headers,
            $payload,
            $payloadJson
        );
    }
}
