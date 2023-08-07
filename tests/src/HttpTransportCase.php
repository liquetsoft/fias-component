<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests;

use Liquetsoft\Fias\Component\Exception\HttpTransportException;
use Liquetsoft\Fias\Component\HttpTransport\HttpTransport;
use Liquetsoft\Fias\Component\HttpTransport\HttpTransportResponse;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Базовый класс для тестов, которые содержат http запросы.
 */
abstract class HttpTransportCase extends BaseCase
{
    public const STATUS_OK = 200;
    public const STATUS_SERVER_ERROR = 500;
    public const ERROR_MESSAGE_JSON = 'json error';

    /**
     * Создает мок для http транспорта.
     *
     * @return HttpTransport&MockObject
     */
    protected function createTransportMock(): HttpTransport
    {
        /** @var HttpTransport&MockObject */
        $transport = $this->getMockBuilder(HttpTransport::class)->getMock();

        return $transport;
    }

    /**
     * Создает мок с ответом 200.
     *
     * @return HttpTransportResponse&MockObject
     */
    protected function createOkResponseMock(string|array $payload = '', bool $isJson = false): HttpTransportResponse
    {
        return $this->createResponseMock(self::STATUS_OK, [], $payload, $isJson);
    }

    /**
     * Создает мок с ответом 200.
     *
     * @return HttpTransportResponse&MockObject
     */
    protected function createBadResponseMock(): HttpTransportResponse
    {
        return $this->createResponseMock(self::STATUS_SERVER_ERROR);
    }

    /**
     * Создает для http ответа.
     *
     * @return HttpTransportResponse&MockObject
     */
    protected function createResponseMock(int $status, array $headers = [], string|array $payload = '', bool $isJson = false): HttpTransportResponse
    {
        /** @var HttpTransportResponse&MockObject */
        $response = $this->getMockBuilder(HttpTransportResponse::class)->getMock();
        $response->method('getStatusCode')->willReturn($status);
        $response->method('isOk')->willReturn($status < 300 && $status >= 200);
        $response->method('getHeaders')->willReturn($headers);
        $response->method('isRangeSupported')->willReturn(($headers['accept-ranges'] ?? '') === 'bytes');
        $response->method('getContentLength')->willReturn((int) ($headers['content-length'] ?? 0));
        $response->method('getPayload')->willReturn(\is_string($payload) ? $payload : json_encode($payload));
        if (\is_array($payload) || $isJson) {
            $response->method('getJsonPayload')->willReturn($payload);
        } else {
            $response->method('getJsonPayload')->willThrowException(
                new HttpTransportException(self::ERROR_MESSAGE_JSON)
            );
        }

        return $response;
    }
}
