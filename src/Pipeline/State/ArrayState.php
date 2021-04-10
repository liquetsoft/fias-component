<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\State;

use InvalidArgumentException;

/**
 * Объект, который хранит состояние во внутреннем массиве.
 */
class ArrayState implements State
{
    /**
     * @var mixed[]
     */
    private $parameters = [];

    /**
     * @var bool
     */
    private $isCompleted = false;

    /**
     * @var string[]
     */
    private $lockedParams = [];

    /**
     * {@inheritdoc}
     */
    public function setParameter(string $parameterName, $parameterValue): State
    {
        $unifiedName = $this->unifyParameterName($parameterName);

        if (\in_array($unifiedName, $this->lockedParams)) {
            throw new InvalidArgumentException(
                "Parameter with name '{$parameterName}' is locked."
            );
        }

        $this->parameters[$unifiedName] = $parameterValue;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAndLockParameter(string $parameterName, $parameterValue): State
    {
        $this->setParameter($parameterName, $parameterValue);
        $this->lockedParams[] = $this->unifyParameterName($parameterName);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter(string $parameterName)
    {
        $unifiedName = $this->unifyParameterName($parameterName);

        return isset($this->parameters[$unifiedName])
            ? $this->parameters[$unifiedName]
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function complete(): State
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

    /**
     * Приводит имя параметра к общему виду, чтобы не плодить разные варианты имен.
     *
     * @param string $parameterName
     *
     * @return string
     */
    protected function unifyParameterName(string $parameterName): string
    {
        return preg_replace('/[^a-z0-9_]+/', '_', strtolower(trim($parameterName)));
    }
}
