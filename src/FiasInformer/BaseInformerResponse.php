<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasInformer;

/**
 * Объект, который предоставляет результат со ссылкой на файлы
 * от сервиса ФИАС.
 */
final class BaseInformerResponse implements InformerResponse
{
    private readonly int $version;

    private readonly string $fullUrl;

    private readonly string $deltaUrl;

    public function __construct(int $version, string $fullUrl, string $deltaUrl)
    {
        $this->version = $version;
        $this->fullUrl = $fullUrl;
        $this->deltaUrl = $deltaUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullUrl(): string
    {
        return $this->fullUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeltaUrl(): string
    {
        return $this->deltaUrl;
    }
}
