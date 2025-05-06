<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasInformer;

use Liquetsoft\Fias\Component\Exception\FiasInformerException;

/**
 * Объект, который предоставляет результат со ссылкой на файлы
 * от сервиса ФИАС.
 *
 * @internal
 */
final class FiasInformerResponseImpl implements FiasInformerResponse
{
    public function __construct(
        private readonly int $version,
        private readonly string $fullUrl,
        private readonly string $deltaUrl,
    ) {
        if ($version <= 0) {
            throw FiasInformerException::create('Version must be more than zero');
        }
        if ($fullUrl !== '') {
            $this->checkUrl($fullUrl);
        }
        if ($deltaUrl !== '') {
            $this->checkUrl($deltaUrl);
        }
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getFullUrl(): string
    {
        return $this->fullUrl;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getDeltaUrl(): string
    {
        return $this->deltaUrl;
    }

    /**
     * Выбрасывает исключение, если ссылка задана в неверном формате.
     */
    private function checkUrl(string $url): void
    {
        if (!preg_match('#https?://.+#', $url)) {
            throw FiasInformerException::create("String '%s' is not an url", $url);
        }
    }
}
