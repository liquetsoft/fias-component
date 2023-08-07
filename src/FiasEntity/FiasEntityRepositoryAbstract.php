<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

use Liquetsoft\Fias\Component\Exception\FiasEntityException;
use Liquetsoft\Fias\Component\Helper\StringHelper;

/**
 * Абстрактный класс для объектов, которые хранят список всех сущностей во внутреннем массиве.
 *
 * @internal
 */
abstract class FiasEntityRepositoryAbstract implements FiasEntityRepository
{
    /**
     * @var array<string, FiasEntity>|null
     */
    private ?array $entities = null;

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
        return $this->loadEntites();
    }

    /**
     * {@inheritdoc}
     */
    public function hasEntity(string $entityName): bool
    {
        $entites = $this->loadEntites();
        $normalizedName = StringHelper::normalize($entityName);

        return isset($entites[$normalizedName]);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity(string $entityName): FiasEntity
    {
        $entites = $this->loadEntites();
        $normalizedName = StringHelper::normalize($entityName);

        if (isset($entites[$normalizedName])) {
            return $entites[$normalizedName];
        }

        throw FiasEntityException::create("Can't find entity with name '%s'", $entityName);
    }

    /**
     * Загружает, если требуется список всех сущностей и возвращает его.
     *
     * @return array<string, FiasEntity>
     */
    private function loadEntites(): array
    {
        if ($this->entities === null) {
            $loadedEntites = $this->loadRepositoryData();
            $this->entities = [];
            foreach ($loadedEntites as $entity) {
                $this->entities[StringHelper::normalize($entity->getName())] = $entity;
            }
        }

        return $this->entities;
    }
}
