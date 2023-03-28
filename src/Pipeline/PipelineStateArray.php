<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline;

/**
 * Объект, который хранит состояние во внутреннем массиве.
 */
final class PipelineStateArray implements PipelineState
{
    public function __construct(private readonly array $params = [])
    {
    }

    /**
     * {@inheritdoc}
     */
    public function with(PipelineStateParam $param, mixed $value): static
    {
        $params = $this->params;
        $params[$param->value] = $value;

        return new self($params);
    }

    /**
     * {@inheritdoc}
     */
    public function without(PipelineStateParam $param): static
    {
        $params = $this->params;
        unset($params[$param->value]);

        return new self($params);
    }

    /**
     * {@inheritdoc}
     */
    public function get(PipelineStateParam $param): mixed
    {
        return $this->params[$param->value] ?? null;
    }
}