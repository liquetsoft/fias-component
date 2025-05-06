<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\State;

/**
 * Объект, который хранит состояние во внутреннем массиве.
 */
final readonly class ArrayState implements State
{
    public function __construct(
        /** @var array<string, mixed> */
        private readonly array $parameters = [],
        private readonly bool $isCompleted = false,
    ) {
        foreach ($this->parameters as $name => $value) {
            if (!StateParameter::tryFrom($name)) {
                throw new \InvalidArgumentException("'{$name}' isn't found in " . StateParameter::class);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setParameter(StateParameter $parameter, mixed $parameterValue): self
    {
        $parameters = $this->parameters;
        $parameters[$parameter->value] = $parameterValue;

        return new self($parameters, $this->isCompleted);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function complete(): self
    {
        return new self($this->parameters, true);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getParameter(StateParameter $parameter, mixed $default = null): mixed
    {
        return $this->parameters[$parameter->value] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getParameterInt(StateParameter $parameter, int $default = 0): int
    {
        return (int) $this->getParameter($parameter, $default);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getParameterString(StateParameter $parameter, string $default = ''): string
    {
        return (string) $this->getParameter($parameter, $default);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }
}
