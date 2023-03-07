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

    private readonly string $url;

    public function __construct(int $version, string $url)
    {
        $this->version = $version;
        $this->url = $url;
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
    public function getUrl(): string
    {
        return $this->url;
    }
}
