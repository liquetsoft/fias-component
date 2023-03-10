<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;

/**
 * Объект, который представляет файл внутри архива.
 */
final class UnpackerEntityImpl implements UnpackerEntity
{
    public function __construct(
        private readonly UnpackerEntityType $type,
        private readonly string $name,
        private readonly int $index,
        private readonly int $size
    ) {
        if (trim($name) === '') {
            throw UnpackerException::create("Entity name can't be empty");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): UnpackerEntityType
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex(): int
    {
        return $this->index;
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
}
