<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Entity;

/**
 * Объект, который описывает поле сущности.
 */
final class BaseEntityField implements EntityField
{
    public function __construct(
        private readonly EntityFieldTypes $type,
        private readonly EntityFieldSubTypes $subType,
        private readonly string $name,
        private readonly string $description,
        private readonly ?int $length,
        private readonly bool $isNullable,
        private readonly bool $isPrimary,
        private readonly bool $isIndex,
        private readonly bool $isPartition
    ) {
        if ($subType->getBaseType() !== null && $subType->getBaseType() !== $type) {
            throw new \InvalidArgumentException('Subtype is not allowed for set type');
        }
        if (trim($name) === '') {
            throw new \InvalidArgumentException('Name is required');
        }
        if ($isPrimary && $isIndex) {
            throw new \InvalidArgumentException('Primary field already has index');
        }
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
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): EntityFieldTypes
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubType(): EntityFieldSubTypes
    {
        return $this->subType;
    }

    /**
     * {@inheritdoc}
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * {@inheritdoc}
     */
    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    /**
     * {@inheritdoc}
     */
    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    /**
     * {@inheritdoc}
     */
    public function isIndex(): bool
    {
        return $this->isIndex;
    }

    /**
     * {@inheritdoc}
     */
    public function isPartition(): bool
    {
        return $this->isPartition;
    }
}
