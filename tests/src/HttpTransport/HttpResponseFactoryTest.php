<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\HttpTransport;

use Liquetsoft\Fias\Component\HttpTransport\HttpResponseFactory;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для фабрики, оторая создает http тветы.
 *
 * @internal
 */
class HttpResponseFactoryTest extends BaseCase
{
    /**
     * Проверяет, что фабрика создаст объект из составных частей.
     */
    public function testCreate(): void
    {
        $code = 200;
        $headers = ['test name' => 'test value'];
        $payload = 'test';

        $response = HttpResponseFactory::create($code, $headers, $payload);

        $this->assertSame($code, $response->getStatusCode());
        $this->assertSame($headers, $response->getHeaders());
        $this->assertSame($payload, $response->getPayload());
    }

    /**
     * Проверяет, что фабрика создаст объект из сырого текста ответа.
     *
     * @dataProvider provideCreateFromText
     */
    public function testCreateFromText(string $response, int $code, array $headers = [], string $payload = ''): void
    {
        $response = HttpResponseFactory::createFromText($response);

        $this->assertSame($code, $response->getStatusCode());
        $this->assertSame($headers, $response->getHeaders());
        $this->assertSame($payload, $response->getPayload());
    }

    public function provideCreateFromText(): array
    {
        return [
            'correct response' => [
                "HTTP/2 200\nContent-Length:123\nx-test:test\n\ntest",
                200,
                ['content-length' => '123', 'x-test' => 'test'],
                'test',
            ],
            'header with semicolon in value' => [
                "HTTP/2 200\nx-test:123:321\n\ntest",
                200,
                ['x-test' => '123:321'],
                'test',
            ],
            'malformed header' => [
                "HTTP/2 200\nx-test\nContent-Length:123\n\ntest",
                200,
                ['content-length' => '123'],
                'test',
            ],
            'blank lines in body' => [
                "HTTP/2 200\nx-test\nContent-Length:123\n\ntest\n\ntest",
                200,
                ['content-length' => '123'],
                "test\n\ntest",
            ],
            'correct response with lower case' => ["http/2 301\n\n", 301],
            'correct response with spaces' => ["       HTTP/2 500\n\n", 500],
            'correct response http 1' => ["HTTP/1 404\n\n", 404],
            'no response' => ['', 0],
            'wrong code response' => ['HTTP/2 abc', 0],
            'tricky response body' => ["HTTP/2 301\n\nHTTP/2 404", 301, [], 'HTTP/2 404'],
            'broken response status' => ["HTTP/2 HTTP/2 301\n\n", 0],
        ];
    }
}
