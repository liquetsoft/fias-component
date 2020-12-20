<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityField;

use InvalidArgumentException;

/**
 * Объект, который описывает поле сущности.
 */
class BaseEntityField implements EntityField
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $subType;

    /**
     * @var int|null
     */
    protected $length;

    /**
     * @var bool
     */
    protected $isNullable;

    /**
     * @var bool
     */
    protected $isPrimary;

    /**
     * @var bool
     */
    protected $isIndex;

    /**
     * @var bool
     */
    protected $isPartition;

    /**
     * @param array $p Массив с описанием поля
     *
     * @throws InvalidArgumentException
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
            throw new InvalidArgumentException(
                'Field is already primary, no needs to set index.'
            );
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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubType(): string
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

    /**
     * Получает указанную строку из набора опций.
     *
     * @param array  $options
     * @param string $name
     * @param bool   $required
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function extractStringFromOptions(array $options, string $name, bool $required = false): string
    {
        $return = '';

        if (!isset($options[$name]) && $required) {
            throw new InvalidArgumentException(
                "Option with key '{$name}' is required for EntityField."
            );
        } elseif (isset($options[$name])) {
            $return = trim((string) $options[$name]);
        }

        return $return;
    }
}
