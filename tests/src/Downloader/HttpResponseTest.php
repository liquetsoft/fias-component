<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Downloader;

use Liquetsoft\Fias\Component\Downloader\HttpResponse;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который хранит http ответ.
 *
 * @internal
 */
class HttpResponseTest extends BaseCase
{
    /**
     * @dataProvider provideGetStatusCode
     */
    public function testGetStatusCode(string $rawResponse, ?int $awaits): void
    {
        $response = new HttpResponse($rawResponse);
        $code = $response->getStatusCode();

        $this->assertSame($awaits, $code);
    }

    public function provideGetStatusCode(): array
    {
        return [
            'correct response' => ["HTTP/2 301\n\ntest", 301],
            'correct response with lower case' => ["http/2 301\n\ntest", 301],
            'correct response with spaces' => ["       HTTP/2 500\n\ntest  ", 500],
            'correct response http 1' => ["HTTP/1 404\n\ntest", 404],
            'no response' => ['', null],
            'wring code response' => ['HTTP/2 abc', null],
            'tricky response body' => ["HTTP/2 301\n\nHTTP/2 404", 301],
            'broken response status' => ["HTTP/2 HTTP/2 301\n\ntest", null],
        ];
    }

    /**
     * @dataProvider provideGetHeaders
     */
    public function testGetHeaders(string $rawResponse, array $awaits): void
    {
        $response = new HttpResponse($rawResponse);
        $headers = $response->getHeaders();

        $this->assertSame($awaits, $headers);
    }

    public function provideGetHeaders(): array
    {
        return [
            'correct headers' => [
                "HTTP/2 301\ncontent-length: 100\nx-test: test\n\ntest",
                [
                    'content-length' => '100',
                    'x-test' => 'test',
                ],
            ],
            'correct headers with spaces' => [
                "HTTP/2 301\n content-length : 100 \n\ntest",
                [
                    'content-length' => '100',
                ],
            ],
            'correct headers with underscores' => [
                "HTTP/2 301\ncontent_length: test_test\n\ntest",
                [
                    'content-length' => 'test_test',
                ],
            ],
            'lower case' => [
                "HTTP/2 301\nX-Test: Test\n\ntest",
                [
                    'x-test' => 'test',
                ],
            ],
            'empty headers' => [
                "HTTP/2 301\n\ntest",
                [],
            ],
            'semicolon in the value' => [
                "HTTP/2 301\nx-test: test:test\n\ntest",
                [
                    'x-test' => 'test:test',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideIsOk
     */
    public function testIsOk(string $rawResponse, bool $awaits): void
    {
        $response = new HttpResponse($rawResponse);
        $isOk = $response->isOk();

        $this->assertSame($awaits, $isOk);
    }

    public function provideIsOk(): array
    {
        return [
            '200 response' => ["HTTP/2 200\n\ntest", true],
            '201 response' => ["HTTP/2 201\n\ntest", true],
            '300 response' => ["HTTP/2 300\n\ntest", false],
            '199 response' => ["HTTP/2 199\n\ntest", false],
            '500 response' => ["HTTP/2 500\n\ntest", false],
            'no response' => ['', false],
        ];
    }

    /**
     * @dataProvider provideGetContentLength
     */
    public function testGetContentLength(string $rawResponse, int $awaits): void
    {
        $response = new HttpResponse($rawResponse);
        $contentLength = $response->getContentLength();

        $this->assertSame($awaits, $contentLength);
    }

    public function provideGetContentLength(): array
    {
        return [
            'content length' => ["HTTP/2 200\nContent-Length:123\n\ntest", 123],
            'no content length' => ["HTTP/2 200\n\ntest", 0],
            'empty content length' => ["HTTP/2 200\nContent-Length:\n\ntest", 0],
            'malformed content length' => ["HTTP/2 200\nContent-Length: qwe\n\ntest", 0],
        ];
    }

    /**
     * @dataProvider provideIsRangeSupported
     */
    public function testIsRangeSupported(string $rawResponse, bool $awaits): void
    {
        $response = new HttpResponse($rawResponse);
        $isRangeSupported = $response->isRangeSupported();

        $this->assertSame($awaits, $isRangeSupported);
    }

    public function provideIsRangeSupported(): array
    {
        return [
            'range supported' => ["HTTP/2 200\nAccept-Ranges:bytes\nContent-Length:123\n\ntest", true],
            'empty content length' => ["HTTP/2 200\nAccept-Ranges:bytes\n\ntest", false],
            'no header' => ["HTTP/2 200\n\ntest", false],
            'empty header' => ["HTTP/2 200\nAccept-Ranges:\nContent-Length:123\n\ntest", false],
            'malformed header' => ["HTTP/2 200\nAccept-Ranges:qwe\nContent-Length:123\n\ntest", false],
        ];
    }
}
