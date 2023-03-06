<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\HttpTransport;

/**
 * Объект, содержащий http ответ.
 */
final class HttpResponse
{
    private readonly int $statusCode;

    /**
     * @var array<string, string>
     */
    private readonly array $headers;

    private readonly string $payload;

    public function __construct(int $statusCode, array $headers = [], string $payload = '')
    {
        $this->statusCode = $statusCode;
        $this->headers = $this->prepareHeaders($headers);
        $this->payload = $payload;
    }

    /**
     * Возвращает код ответа.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Возвращает заголовки ответа.
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Возвращает правду, если ответ был успешным.
     */
    public function isOk(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Возвращает длину тела ответ.
     */
    public function getContentLength(): int
    {
        return (int) ($this->headers['content-length'] ?? 0);
    }

    /**
     * Возвращает правду, если сервер поддерживает докачку файла.
     */
    public function isRangeSupported(): bool
    {
        return $this->getContentLength() > 0 && ($this->headers['accept-ranges'] ?? '') === 'bytes';
    }

    /**
     * Возвращает тело ответ.
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * Подготавливает заголовки для использования.
     *
     * @return array<string, string>
     */
    private function prepareHeaders(array $headers): array
    {
        $preparedHeaders = [];
        foreach ($headers as $name => $value) {
            $name = str_replace('_', '-', strtolower(trim((string) $name)));
            $value = strtolower(trim((string) $value));
            $preparedHeaders[$name] = $value;
        }

        return $preparedHeaders;
    }
}
