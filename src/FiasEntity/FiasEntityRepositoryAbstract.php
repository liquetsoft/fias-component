<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

use Liquetsoft\Fias\Component\Exception\FiasEntityException;

/**
 * Абстрактный класс для объектов, которые хранят список всех сущностей во внутреннем массиве.
 */
abstract class FiasEntityRepositoryAbstract implements FiasEntityRepository
{
    /**
     * @var iterable<FiasEntity>|null
     */
    private ?iterable $entities = null;

    /**
     * Возвращает полностью подготовленный массив с описаниями сущностей.
     *
     * @return iterable<FiasEntity>
     */
    abstract protected function loadRepositoryData(): iterable;

    /**
     * {@inheritdoc}
     */
    public function getAllEntities(): iterable
    {
        if ($this->entities === null) {
            $this->entities = $this->loadRepositoryData();
        }

        return $this->entities;
    }

    /**
     * {@inheritdoc}
     */
    public function hasEntity(string $entityName): bool
    {
        $unifiedName = $this->unifyEntityName($entityName);

        foreach ($this->getAllEntities() as $entity) {
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

        foreach ($this->getAllEntities() as $entity) {
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
