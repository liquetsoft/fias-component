<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

use Liquetsoft\Fias\Component\Helper\StringHelper;

/**
 * Объект, который содержит соответствия между сущностями ФИАС и их
 * реализациями в конкретном проекте.
 */
final class FiasEntityBinderImpl implements FiasEntityBinder
{
    private readonly FiasEntityRepository $repo;

    /**
     * @psalm-var array<string, class-string>
     */
    private readonly array $bindings;

    /**
     * @psalm-param array<string, class-string> $bindings
     */
    public function __construct(FiasEntityRepository $repo, array $bindings)
    {
        $this->repo = $repo;

        $normalizedBindings = [];
        foreach ($bindings as $entityName => $boundClass) {
            $normalizedName = StringHelper::normalize($entityName);
            $normalizedBindings[$normalizedName] = $boundClass;
        }
        $this->bindings = $normalizedBindings;
    }

    /**
     * {@inheritdoc}
     */
    public function getImplementationByEntityName(FiasEntity|string $entity): ?string
    {
        $entityName = $entity instanceof FiasEntity ? $entity->getName() : $entity;
        $entityName = StringHelper::normalize($entityName);

        return $this->bindings[$entityName] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityByImplementation(string|object $implementation): ?FiasEntity
    {
        $className = \is_object($implementation) ? \get_class($implementation) : $implementation;
        $entityName = array_search($className, $this->bindings);

        return \is_string($entityName) && $this->repo->hasEntity($entityName)
            ? $this->repo->getEntity($entityName)
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getBoundEntities(): array
    {
        $result = [];
        foreach ($this->bindings as $entityName => $boundClass) {
            if ($this->repo->hasEntity($entityName)) {
                $result[] = $this->repo->getEntity($entityName);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
}
