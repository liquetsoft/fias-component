<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

use Liquetsoft\Fias\Component\Exception\FiasEntityException;

/**
 * Объект, который хранит описание сущности ФИАС.
 *
 * @internal
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
            throw FiasEntityException::create('Name param is required');
        }

        if (trim($xmlPath) === '') {
            throw FiasEntityException::create('XmlPath param is required');
        }

        if (empty($fields)) {
            throw FiasEntityException::create("Fields array can't be empty");
        }
        $fieldNames = [];
        foreach ($fields as $field) {
            if (\in_array($field->getName(), $fieldNames)) {
                throw FiasEntityException::create('All fields names must be unique, got duplicate: %s', $field->getName());
            }
            $fieldNames[] = $field->getName();
        }

        if ($partitionsCount < 1) {
            throw FiasEntityException::create("Partititons count can't be less than 1");
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

        throw FiasEntityException::create("FiasEntity doesn't have field with name '%s'", $name);
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
        if (empty($mask)) {
            return false;
        }

        if (preg_match('/(^\/.+\/$)|(^#.*#$)/', $mask)) {
            $pattern = $mask;
        } else {
            $delimiter = '/';
            $quotedParts = array_map(
                fn (string $s): string => preg_quote($s, $delimiter),
                explode('*', $mask)
            );
            $pattern = $delimiter . '^' . implode('[0-9a-zA-Z\-_]+', $quotedParts) . '$' . $delimiter . 'i';
        }

        return preg_match($pattern, $fileName) === 1;
    }
}
