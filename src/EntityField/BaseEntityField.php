<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityField;

/**
 * Объект, который описывает поле сущности.
 */
final class BaseEntityField implements EntityField
{
    protected string $name;

    protected string $description;

    protected string $type;

    protected string $subType;

    protected ?int $length;

    protected bool $isNullable;

    protected bool $isPrimary;

    protected bool $isIndex;

    protected bool $isPartition;

    /**
     * @param array $p Массив с описанием поля
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $p)
    {
        $this->name = $this->extractStringFromOptions($p, 'name', true);
        $this->description = $this->extractStringFromOptions($p, 'description');
        $this->type = $this->extractStringFromOptions($p, 'type', true);
        $this->subType = $this->extractStringFromOptions($p, 'subType');
        $this->length = isset($p['length']) ? (int) $p['length'] : null;
        $this->isNullable = !empty($p['isNullable']);
        $this->isPrimary = !empty($p['isPrimary']);
        $this->isIndex = !empty($p['isIndex']);
        $this->isPartition = !empty($p['isPartition']);

        if ($this->isPrimary && $this->isIndex) {
            throw new \InvalidArgumentException(
                'Field is already primary, no needs to set index.'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getSubType(): string
    {
        return $this->subType;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isIndex(): bool
    {
        return $this->isIndex;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isPartition(): bool
    {
        return $this->isPartition;
    }

    /**
     * Получает указанную строку из набора опций.
     *
     * @throws \InvalidArgumentException
     */
    protected function extractStringFromOptions(array $options, string $name, bool $required = false): string
    {
        $return = '';

        if (!isset($options[$name]) && $required) {
            throw new \InvalidArgumentException(
                "Option with key '{$name}' is required for EntityField."
            );
        } elseif (isset($options[$name])) {
            $return = trim((string) $options[$name]);
        }

        return $return;
    }
}
