<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

use Liquetsoft\Fias\Component\Exception\FiasEntityException;

/**
 * Объект, который описывает поле сущности.
 */
final class FiasEntityFieldImpl implements FiasEntityField
{
    public function __construct(
        private readonly FiasEntityFieldType $type,
        private readonly FiasEntityFieldSubType $subType,
        private readonly string $name,
        private readonly string $description,
        private readonly ?int $length,
        private readonly bool $isNullable,
        private readonly bool $isPrimary,
        private readonly bool $isIndex,
        private readonly bool $isPartition
    ) {
        if ($subType->getBaseType() !== null && $subType->getBaseType() !== $type) {
            throw FiasEntityException::create('Subtype is not allowed for set type');
        }
        if (trim($name) === '') {
            throw FiasEntityException::create('Name is required');
        }
        if ($isPrimary && $isIndex) {
            throw FiasEntityException::create('Primary field already has index');
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
    public function getType(): FiasEntityFieldType
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubType(): FiasEntityFieldSubType
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
