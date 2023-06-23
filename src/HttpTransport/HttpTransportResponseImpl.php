<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\HttpTransport;

use Liquetsoft\Fias\Component\Exception\HttpTransportException;

/**
 * Объект, содержащий http ответ.
 *
 * @internal
 */
final class HttpTransportResponseImpl implements HttpTransportResponse
{
    private const JSON_DEPTH = 512;

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
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function isOk(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentLength(): int
    {
        return (int) ($this->headers['content-length'] ?? 0);
    }

    /**
     * {@inheritdoc}
     */
    public function isRangeSupported(): bool
    {
        return $this->getContentLength() > 0 && ($this->headers['accept-ranges'] ?? '') === 'bytes';
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonPayload(): mixed
    {
        if (empty($this->headers['content-type']) || !str_contains($this->headers['content-type'], 'json')) {
            throw HttpTransportException::create('Payload is not a json');
        }

        try {
            $res = json_decode($this->payload, true, self::JSON_DEPTH, \JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            throw HttpTransportException::wrap($e);
        }

        return $res;
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
