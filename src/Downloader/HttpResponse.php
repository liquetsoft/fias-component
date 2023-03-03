<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Downloader;

/**
 * Объект, содержащий http ответ.
 *
 * @internal
 */
final class HttpResponse
{
    private readonly ?int $statusCode;

    /**
     * @var array<string, string>
     */
    private readonly array $headers;

    public function __construct(mixed $rawResponse = null)
    {
        $response = trim((string) $rawResponse);
        $this->statusCode = $this->extractStatusCodeFromResponseBody($response);
        $this->headers = $this->extractHeadersFromContent($response);
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function isOk(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function getContentLength(): int
    {
        return (int) ($this->headers['content-length'] ?? 0);
    }

    public function isRangeSupported(): bool
    {
        return $this->getContentLength() > 0 && ($this->headers['accept-ranges'] ?? '') === 'bytes';
    }

    /**
     * @return array<string, string>
     */
    private function extractHeadersFromContent(string $response): array
    {
        $explodedResponse = explode("\n\n", $response);

        $headers = [];
        $rawHeaders = explode("\n", $explodedResponse[0]);
        foreach ($rawHeaders as $rawHeader) {
            $rawHeaderExplode = explode(':', $rawHeader, 2);
            if (\count($rawHeaderExplode) < 2) {
                continue;
            }
            $name = str_replace('_', '-', strtolower(trim($rawHeaderExplode[0])));
            $value = strtolower(trim($rawHeaderExplode[1]));
            $headers[$name] = $value;
        }

        return $headers;
    }

    private function extractStatusCodeFromResponseBody(string $body): ?int
    {
        if (preg_match('#^HTTP/\S+\s+([0-9]{3})#i', $body, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }
}
