<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\HttpTransport;

/**
 * Объект, содержащий http ответ.
 *
 * @internal
 */
final class HttpTransportResponseImpl implements HttpTransportResponse
{
    /**
     * @var array<string, string>
     */
    private readonly array $headers;

    public function __construct(
        private readonly int $statusCode,
        array $headers = [],
        private readonly string $payload = '',
        private readonly mixed $payloadJson = null,
    ) {
        $this->headers = $this->prepareHeaders($headers);
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
        return $this->payloadJson;
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
