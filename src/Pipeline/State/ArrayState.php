<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\State;

/**
 * Объект, который хранит состояние во внутреннем массиве.
 */
final class ArrayState implements State
{
    private array $parameters = [];

    private bool $isCompleted = false;

    /**
     * @var StateParameter[]
     */
    private array $lockedParams = [];

    /**
     * {@inheritdoc}
     */
    public function setParameter(StateParameter $parameter, mixed $parameterValue): State
    {
        if (\in_array($parameter, $this->lockedParams, true)) {
            throw new \InvalidArgumentException(
                "Parameter with name '{$parameter->value}' is locked"
            );
        }

        $this->parameters[$parameter->value] = $parameterValue;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAndLockParameter(StateParameter $parameter, mixed $parameterValue): State
    {
        $this->setParameter($parameter, $parameterValue);
        $this->lockedParams[] = $parameter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter(StateParameter $parameter, mixed $default = null): mixed
    {
        return $this->parameters[$parameter->value] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterInt(StateParameter $parameter, int $default = 0): int
    {
        return (int) $this->getParameter($parameter, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterString(StateParameter $parameter, string $default = ''): string
    {
        return (string) $this->getParameter($parameter, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function complete(): self
    {
        $this->isCompleted = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }
}
