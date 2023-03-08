<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\HttpTransport;

use Liquetsoft\Fias\Component\Exception\HttpTransportException;
use Liquetsoft\Fias\Component\HttpTransport\BaseHttpResponse;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который содержит http ответ.
 *
 * @internal
 */
class BaseHttpResponseTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно вернет код ответа.
     */
    public function testGetStatusCode(): void
    {
        $code = 304;

        $response = new BaseHttpResponse($code);
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

        $response = new BaseHttpResponse($code, $headers);
        $responseHeaders = $response->getHeaders();

        $this->assertSame($awaits, $responseHeaders);
    }

    public function provideGetHeaders(): array
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

        $response = new BaseHttpResponse($code, $headers, $payload);
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
        $response = new BaseHttpResponse($code);
        $isOk = $response->isOk();

        $this->assertSame($awaits, $isOk);
    }

    public function provideIsOk(): array
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
        $response = new BaseHttpResponse(200, $headers);
        $contentLength = $response->getContentLength();

        $this->assertSame($awaits, $contentLength);
    }

    public function provideGetContentLength(): array
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
        $response = new BaseHttpResponse(200, $headers);
        $isRangeSupported = $response->isRangeSupported();

        $this->assertSame($awaits, $isRangeSupported);
    }

    public function provideIsRangeSupported(): array
    {
        return [
            'range supported' => [['Accept-Ranges' => 'bytes', 'Content-Length' => 123], true],
            'empty content length' => [['Accept-Ranges' => 'bytes'], false],
            'no header' => [[], false],
            'empty header' => [['Accept-Ranges' => '', 'Content-Length' => 123], false],
            'malformed header' => [['Accept-Ranges' => 'qwe', 'Content-Length' => 123], false],
        ];
    }

    /**
     * Проверяет, что объект вернет тело json ответа в виде массива.
     *
     * @dataProvider provideGetJsonPayload
     */
    public function testGetJsonPayload(array $headers, string $payload, mixed $awaits): void
    {
        $response = new BaseHttpResponse(200, $headers, $payload);

        if ($awaits instanceof \Exception) {
            $this->expectExceptionObject($awaits);
        }
        $jsonPayload = $response->getJsonPayload();

        if (\is_array($awaits)) {
            $this->assertSame($awaits, $jsonPayload);
        }
    }

    public function provideGetJsonPayload(): array
    {
        return [
            'correct json payload' => [
                ['content-type' => 'application/json; charset=utf-8'],
                '{"VersionId":20230307,"TextVersion":"БД ФИАС от 07.03.2023"}',
                [
                    'VersionId' => 20230307,
                    'TextVersion' => 'БД ФИАС от 07.03.2023',
                ],
            ],
            'no content type header' => [
                [],
                '{test: "test value"}',
                new HttpTransportException('Payload is not a json'),
            ],
            'non json payload' => [
                ['content-type' => 'text/html'],
                '{test: "test value"}',
                new HttpTransportException('Payload is not a json'),
            ],
            'malformed payload' => [
                ['content-type' => 'application/json; charset=utf-8'],
                '{test: "test va',
                new HttpTransportException('Syntax error', 4),
            ],
        ];
    }
}
