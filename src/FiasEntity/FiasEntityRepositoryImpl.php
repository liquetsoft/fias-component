<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

use Liquetsoft\Fias\Component\Exception\FiasEntityException;

/**
 * Объект, который хранит список всех сущностей во внутреннем массиве.
 */
final class FiasEntityRepositoryImpl implements FiasEntityRepository
{
    public function __construct(
        /** @var iterable<FiasEntity> */
        private readonly iterable $entities
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getAllEntities(): iterable
    {
        return $this->entities;
    }

    /**
     * {@inheritdoc}
     */
    public function hasEntity(string $entityName): bool
    {
        $unifiedName = $this->unifyEntityName($entityName);

        foreach ($this->entities as $entity) {
            if ($this->unifyEntityName($entity->getName()) === $unifiedName) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity(string $entityName): FiasEntity
    {
        $unifiedName = $this->unifyEntityName($entityName);

        foreach ($this->entities as $entity) {
            if ($this->unifyEntityName($entity->getName()) === $unifiedName) {
                return $entity;
            }
        }

        throw FiasEntityException::create("Can't find entity with name '%s'", $entityName);
    }

    /**
     * Приводит имена сущностей к единому виду.
     */
    private function unifyEntityName(string $name): string
    {
        return strtolower(trim($name));
    }
}
