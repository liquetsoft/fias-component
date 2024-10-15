<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasFile;

/**
 * Объект, который представляет файл.
 *
 * @internal
 */
final readonly class FiasFileImpl implements FiasFile
{
    public function __construct(
        private readonly string $name,
        private readonly int $size,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
