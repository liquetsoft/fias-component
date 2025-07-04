<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityDescriptor;

use Liquetsoft\Fias\Component\EntityField\EntityField;

/**
 * Объект, который хранит описание сущности ФИАС.
 */
final class BaseEntityDescriptor implements EntityDescriptor
{
    protected string $name;

    protected string $description = '';

    protected int $partitionsCount = 1;

    protected string $xmlPath = '';

    protected string $insertFileMask = '';

    protected string $deleteFileMask = '';

    /**
     * @var EntityField[]
     */
    protected array $fields;

    /**
     * @param array $p Массив с описанием сущности
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $p)
    {
        $this->name = $this->extractStringFromOptions($p, 'name', true);
        $this->xmlPath = $this->extractStringFromOptions($p, 'xmlPath', true);
        $this->description = $this->extractStringFromOptions($p, 'description');
        $this->insertFileMask = $this->extractStringFromOptions($p, 'insertFileMask');
        $this->deleteFileMask = $this->extractStringFromOptions($p, 'deleteFileMask');
        $this->partitionsCount = isset($p['partitionsCount']) ? (int) $p['partitionsCount'] : 1;
        $this->fields = $this->extractFieldsFromOptions($p);
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
    public function getPartitionsCount(): int
    {
        return $this->partitionsCount;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getXmlPath(): string
    {
        return $this->xmlPath;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getXmlInsertFileMask(): string
    {
        return $this->insertFileMask;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getXmlDeleteFileMask(): string
    {
        return $this->deleteFileMask;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function hasField(string $name): bool
    {
        $return = false;

        foreach ($this->fields as $field) {
            if ($field->getName() === $name) {
                $return = true;
                break;
            }
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getField(string $name): EntityField
    {
        $return = null;

        foreach ($this->fields as $field) {
            if ($field->getName() === $name) {
                $return = $field;
                break;
            }
        }

        if (!$return) {
            throw new \InvalidArgumentException(
                "EntityDescriptor doesn't have field with name '{$name}'."
            );
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isFileNameFitsXmlInsertFileMask(string $fileName): bool
    {
        return $this->isFileNameFitsMask($fileName, $this->insertFileMask);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isFileNameFitsXmlDeleteFileMask(string $fileName): bool
    {
        return $this->isFileNameFitsMask($fileName, $this->deleteFileMask);
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
                "Option with key '{$name}' is required for EntityDescriptor."
            );
        } elseif (isset($options[$name])) {
            $return = trim((string) $options[$name]);
        }

        return $return;
    }

    /**
     * Возвращает список полей из массива опций.
     *
     * @return EntityField[]
     *
     * @throws \InvalidArgumentException
     */
    protected function extractFieldsFromOptions(array $options): array
    {
        $return = [];

        if (empty($options['fields']) || !\is_array($options['fields'])) {
            throw new \InvalidArgumentException(
                'Fields is required option for EntityDescriptor.'
            );
        }

        foreach ($options['fields'] as $key => $field) {
            if (!($field instanceof EntityField)) {
                throw new \InvalidArgumentException(
                    "Field with key '{$key}' must be an '" . EntityField::class . "' instance."
                );
            }
            if (isset($return[$field->getName()])) {
                throw new \InvalidArgumentException(
                    "Field with key '{$key}' has doubling name '" . $field->getName() . "'."
                );
            }
            $return[$field->getName()] = $field;
        }

        return array_values($return);
    }

    /**
     * Сравнивает маску имени файла с именем.
     */
    protected function isFileNameFitsMask(string $fileName, string $mask): bool
    {
        if ($mask === '') {
            return false;
        }

        if (preg_match('/^(\/.+\/)|(#.*#)[a-z]*$/', $mask)) {
            $pattern = $mask;
        } else {
            $pattern = '/^' . implode('[0-9a-zA-Z\-]+', array_map('preg_quote', explode('*', $mask))) . '$/i';
        }

        return preg_match($pattern, $fileName) === 1;
    }
}
