<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\HttpTransport;

use Liquetsoft\Fias\Component\HttpTransport\HttpTransportResponseFactory;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для фабрики, оторая создает http тветы.
 *
 * @internal
 */
class HttpTransportResponseFactoryTest extends BaseCase
{
    /**
     * Проверяет, что фабрика создаст объект из составных частей.
     */
    public function testCreate(): void
    {
        $code = 200;
        $headers = [
            'test name' => 'test value',
        ];
        $payload = 'test';
        $payloadJson = [
            'test name payload' => 'test value payload',
        ];

        $response = HttpTransportResponseFactory::create($code, $headers, $payload, $payloadJson);

        $this->assertSame($code, $response->getStatusCode());
        $this->assertSame($headers, $response->getHeaders());
        $this->assertSame($payload, $response->getPayload());
        $this->assertSame($payloadJson, $response->getJsonPayload());
    }
}
