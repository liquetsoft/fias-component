<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Downloader;

/**
 * Объект, содержащий ответ от curl.
 */
class CurlDownloaderResponse
{
    private int $statusCode;

    private bool $isOk;

    private ?string $error;

    /**
     * @var array<string, string>
     */
    private array $headers;

    public function __construct(?array $rawCurlResponse = null)
    {
        $this->statusCode = isset($rawCurlResponse[0]) ? (int) $rawCurlResponse[0] : 0;
        $this->isOk = $this->statusCode >= 200 && $this->statusCode < 300;
        $this->headers = isset($rawCurlResponse[1]) ? $this->extractHeadersFromContent($rawCurlResponse[1]) : [];
        $this->error = isset($rawCurlResponse[2]) ? (string) $rawCurlResponse[2] : null;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function isOk(): bool
    {
        return $this->isOk;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Получает список заголовков из http ответа.
     *
     * @param mixed $content
     *
     * @return array<string, string>
     */
    private function extractHeadersFromContent($content): array
    {
        if (!\is_string($content)) {
            return [];
        }

        $explodeHeadersContent = explode("\n\n", $content, 2);

        $headers = [];
        $rawHeaders = explode("\n", $explodeHeadersContent[0]);
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
}
