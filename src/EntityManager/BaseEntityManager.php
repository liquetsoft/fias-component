<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityManager;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityRegistry\EntityRegistry;
use InvalidArgumentException;

/**
 * Объект, который содержит соответствия между сущностями ФИАС и их реализациями
 * в конкретном проекте.
 */
class BaseEntityManager implements EntityManager
{
    /**
     * @var EntityRegistry
     */
    protected $registry;

    /**
     * @var string[]
     */
    protected $bindings;

    /**
     * @param EntityRegistry $registry
     * @param string[]       $bindings
     *
     * @throws InvalidArgumentException
     */
    public function __construct(EntityRegistry $registry, array $bindings)
    {
        $this->registry = $registry;

        $this->bindings = [];
        foreach ($bindings as $entityName => $className) {
            $clasName = trim($className, '\\ ');
            if ($clasName === '') {
                throw new InvalidArgumentException(
                    "There is no class for {$entityName} entity name."
                );
            }
            $this->bindings[$this->normalizeEntityName($entityName)] = $clasName;
        }
    }

    /**
     * @inheritdoc
     */
    public function getDescriptorByEntityName(string $entityName): ?EntityDescriptor
    {
        $return = null;

        $normalizedEntityName = $this->normalizeEntityName($entityName);
        if (isset($this->bindings[$normalizedEntityName]) && $this->registry->hasDescriptor($entityName)) {
            $return = $this->registry->getDescriptor($entityName);
        }

        return $return;
    }

    /**
     * @inheritdoc
     */
    public function getClassByDescriptor(EntityDescriptor $descriptor): ?string
    {
        $normalizedEntityName = $this->normalizeEntityName($descriptor->getName());

        return $this->bindings[$normalizedEntityName] ?? null;
    }

    /**
     * Приводит имена сущностей к единообразному виду.
     *
     * @param string $name
     *
     * @return string
     */
    public function normalizeEntityName(string $name): string
    {
        return trim(strtolower($name));
    }
}
