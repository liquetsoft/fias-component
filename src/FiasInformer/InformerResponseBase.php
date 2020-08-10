<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasInformer;

use InvalidArgumentException;

/**
 * Объект, который предоставляет результат со ссылкой на файлы
 * от сервиса ФИАС.
 */
class InformerResponseBase implements InformerResponse
{
    /**
     * @var int
     */
    protected $version = 0;

    /**
     * @var string
     */
    protected $url = '';

    /**
     * @inheritdoc
     */
    public function setVersion(int $version): InformerResponse
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public function validateUrl(string $url)
    {
        if (!preg_match('#https?://.+\.[^.]+.*#', $url)) {
            return false;
        } else {
            return $url;
        }
    }

    /**
     * @inheritdoc
     */
    public function setUrl(string $url): InformerResponse
    {
        if ($this->validateUrl($url) === false) {
            throw new InvalidArgumentException("Wrong url format: {$url}");
        }
        $this->url = $url;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @inheritdoc
     */
    public function hasResult(): bool
    {
        return $this->url !== '' && $this->version !== 0;
    }
}
