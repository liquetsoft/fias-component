<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

/**
 * Объект, который хранит описание сущности ФИАС.
 */
final class FiasEntityImpl implements FiasEntity
{
    public function __construct(
        private readonly string $name,
        private readonly string $xmlPath,
        /** @var iterable<FiasEntityField> */
        private readonly iterable $fields,
        private readonly string $description,
        private readonly int $partitionsCount,
        private readonly string $insertFileMask,
        private readonly string $deleteFileMask
    ) {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('Name param is required');
        }

        if (trim($xmlPath) === '') {
            throw new \InvalidArgumentException('XmlPath param is required');
        }

        if (empty($fields)) {
            throw new \InvalidArgumentException("Fields array can't be empty");
        }
        $fieldNames = [];
        foreach ($fields as $field) {
            if (isset($fieldNames[$field->getName()])) {
                throw new \InvalidArgumentException('All fields names must be unique, got duplicate: ' . $field->getName());
            }
            $fieldNames[$field->getName()] = 1;
        }

        if ($partitionsCount < 1) {
            throw new \InvalidArgumentException("Partititons count can't be less than 1");
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
    public function getPartitionsCount(): int
    {
        return $this->partitionsCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getXmlPath(): string
    {
        return $this->xmlPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getXmlInsertFileMask(): string
    {
        return $this->insertFileMask;
    }

    /**
     * {@inheritdoc}
     */
    public function getXmlDeleteFileMask(): string
    {
        return $this->deleteFileMask;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields(): iterable
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    public function hasField(string $name): bool
    {
        foreach ($this->fields as $field) {
            if ($field->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getField(string $name): FiasEntityField
    {
        foreach ($this->fields as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }

        throw new \InvalidArgumentException(
            "FiasEntity doesn't have field with name '{$name}'."
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isFileNameFitsXmlInsertFileMask(string $fileName): bool
    {
        return $this->isFileNameFitsMask($fileName, $this->insertFileMask);
    }

    /**
     * {@inheritdoc}
     */
    public function isFileNameFitsXmlDeleteFileMask(string $fileName): bool
    {
        return $this->isFileNameFitsMask($fileName, $this->deleteFileMask);
    }

    /**
     * Сравнивает маску имени файла с именем.
     */
    private function isFileNameFitsMask(string $fileName, string $mask): bool
    {
        if (preg_match('/^(\/.+\/)|(#.*#)[a-z]*$/', $mask)) {
            $pattern = $mask;
        } else {
            $pattern = '/^' . implode('[0-9a-zA-Z\-]+', array_map('preg_quote', explode('*', $mask))) . '$/i';
        }

        return preg_match($pattern, $fileName) === 1;
    }
}
