<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityDescriptor;

use InvalidArgumentException;

/**
 * Объект, который хранит описание сущности.
 */
class BaseEntityDescriptor implements EntityDescriptor
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var int
     */
    protected $partitionsCount = 1;

    /**
     * @var string
     */
    protected $xmlPath = '';

    /**
     * @var string
     */
    protected $insertFileMask = '';

    /**
     * @var string
     */
    protected $deleteFileMask = '';

    /**
     * @param array $p Массив с описанием сущности
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $p)
    {
        if (empty($p['name'])) {
            throw new InvalidArgumentException(
                'Name is required parameter for descriptor'
            );
        } else {
            $this->name = trim($p['name']);
        }

        if (empty($p['xmlPath'])) {
            throw new InvalidArgumentException(
                'XmlPath is required parameter for descriptor'
            );
        } else {
            $this->xmlPath = trim($p['xmlPath']);
        }

        if (!empty($p['description'])) {
            $this->description = trim($p['description']);
        }

        if (!empty($p['partitionsCount'])) {
            $this->partitionsCount = (int) $p['partitionsCount'];
        }

        if (!empty($p['insertFileMask'])) {
            $this->insertFileMask = trim($p['insertFileMask']);
        }

        if (!empty($p['deleteFileMask'])) {
            $this->deleteFileMask = trim($p['deleteFileMask']);
        }
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function getPartitionsCount(): int
    {
        return $this->partitionsCount;
    }

    /**
     * @inheritdoc
     */
    public function getXmlPath(): string
    {
        return $this->xmlPath;
    }

    /**
     * @inheritdoc
     */
    public function getXmlInsertFileMask(): string
    {
        return $this->insertFileMask;
    }

    /**
     * @inheritdoc
     */
    public function getXmlDeleteFileMask(): string
    {
        return $this->deleteFileMask;
    }
}
