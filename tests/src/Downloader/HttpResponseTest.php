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
            'correct response with spaces' => ["       HTTP/2 500\n\ntest  ", 500],
            'correct response http 1' => ["HTTP/1 404\n\ntest", 404],
            'no response' => ['', null],
            'wring code response' => ['HTTP/2 abc', null],
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
            'lower case' => [
                "HTTP/2 301\n X-Test : Test \n\ntest",
                [
                    'x-test' => 'test',
                ],
            ],
            'empty headers' => [
                "HTTP/2 301\n\ntest",
                [],
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
            'ok response' => ["HTTP/2 200\n\ntest", true],
            'created response' => ["HTTP/2 201\n\ntest", true],
            'server error response' => ["HTTP/2 500\n\ntest", false],
            'no response' => ['', false],
        ];
    }
}
