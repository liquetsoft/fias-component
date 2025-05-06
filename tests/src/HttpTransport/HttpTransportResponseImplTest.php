<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\HttpTransport;

use Liquetsoft\Fias\Component\HttpTransport\HttpTransportResponseImpl;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который содержит http ответ.
 *
 * @internal
 */
final class HttpTransportResponseImplTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно вернет код ответа.
     */
    public function testGetStatusCode(): void
    {
        $code = 304;

        $response = new HttpTransportResponseImpl($code);
        $responseStatusCode = $response->getStatusCode();

        $this->assertSame($code, $responseStatusCode);
    }

    /**
     * Проверяет, что объект правильно вернет заголовки ответа.
     *
     * @dataProvider provideGetHeaders
     */
    public function testGetHeaders(array $headers, array $awaits): void
    {
        $code = 304;

        $response = new HttpTransportResponseImpl($code, $headers);
        $responseHeaders = $response->getHeaders();

        $this->assertSame($awaits, $responseHeaders);
    }

    public static function provideGetHeaders(): array
    {
        return [
            'correct headers' => [
                [
                    'content-length' => 100,
                    'x-test' => 'test',
                ],
                [
                    'content-length' => '100',
                    'x-test' => 'test',
                ],
            ],
            'correct headers with spaces' => [
                [
                    '  content-length  ' => '  100  ',
                ],
                [
                    'content-length' => '100',
                ],
            ],
            'correct headers with underscores' => [
                [
                    'content_length' => '100',
                ],
                [
                    'content-length' => '100',
                ],
            ],
            'upper/lower case' => [
                [
                    'Content-Length' => 'BYTES',
                ],
                [
                    'content-length' => 'bytes',
                ],
            ],
            'numeric name' => [
                [
                    123 => '100',
                ],
                [
                    '123' => '100',
                ],
            ],
            'empty headers' => [
                [],
                [],
            ],
        ];
    }

    /**
     * Проверяет, что объект правильно вернет тело ответа.
     */
    public function testGetPayload(): void
    {
        $code = 304;
        $headers = [];
        $payload = 'test';

        $response = new HttpTransportResponseImpl($code, $headers, $payload);
        $responsePayload = $response->getPayload();

        $this->assertSame($payload, $responsePayload);
    }

    /**
     * Проверяет, что объект вернет правду, если запрос был успешным.
     *
     * @dataProvider provideIsOk
     */
    public function testIsOk(int $code, bool $awaits): void
    {
        $response = new HttpTransportResponseImpl($code);
        $isOk = $response->isOk();

        $this->assertSame($awaits, $isOk);
    }

    public static function provideIsOk(): array
    {
        return [
            '199 response' => [199, false],
            '200 response' => [200, true],
            '201 response' => [201, true],
            '300 response' => [300, false],
        ];
    }

    /**
     * Проверяет, что объект вернет правильное значение заголовка Content-Length.
     *
     * @dataProvider provideGetContentLength
     */
    public function testGetContentLength(array $headers, int $awaits): void
    {
        $response = new HttpTransportResponseImpl(200, $headers);
        $contentLength = $response->getContentLength();

        $this->assertSame($awaits, $contentLength);
    }

    public static function provideGetContentLength(): array
    {
        return [
            'content length' => [['Content-Length' => '123'], 123],
            'no content length' => [[], 0],
            'empty content length' => [['Content-Length' => ''], 0],
            'malformed content length' => [['Content-Length' => 'qwe'], 0],
        ];
    }

    /**
     * Проверяет, что объект вернет правильное значение заголовка Accept-Ranges.
     *
     * @dataProvider provideIsRangeSupported
     */
    public function testIsRangeSupported(array $headers, bool $awaits): void
    {
        $response = new HttpTransportResponseImpl(200, $headers);
        $isRangeSupported = $response->isRangeSupported();

        $this->assertSame($awaits, $isRangeSupported);
    }

    public static function provideIsRangeSupported(): array
    {
        return [
            'range supported' => [
                [
                    'Accept-Ranges' => 'bytes',
                    'Content-Length' => 123,
                ],
                true,
            ],
            'empty content length' => [
                [
                    'Accept-Ranges' => 'bytes',
                ],
                false,
            ],
            'no header' => [
                [],
                false,
            ],
            'empty header' => [
                [
                    'Accept-Ranges' => '',
                    'Content-Length' => 123,
                ],
                false,
            ],
            'malformed header' => [
                [
                    'Accept-Ranges' => 'qwe',
                    'Content-Length' => 123,
                ],
                false,
            ],
        ];
    }

    /**
     * Проверяет, что объект вернет тело json ответа в виде массива.
     */
    public function testGetJsonPayload(): void
    {
        $code = 304;
        $headers = [];
        $payload = 'test';
        $payloadJson = [
            'key' => 'value',
        ];

        $response = new HttpTransportResponseImpl($code, $headers, $payload, $payloadJson);
        $responsePayload = $response->getJsonPayload();

        $this->assertSame($payloadJson, $responsePayload);
    }
}
