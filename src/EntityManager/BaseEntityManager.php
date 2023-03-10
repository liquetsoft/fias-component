<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityManager;

use Liquetsoft\Fias\Component\EntityRegistry\EntityRegistry;
use Liquetsoft\Fias\Component\Exception\EntityRegistryException;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntity;

/**
 * Объект, который содержит соответствия между сущностями ФИАС и их реализациями
 * в конкретном проекте.
 */
class BaseEntityManager implements EntityManager
{
    protected EntityRegistry $registry;

    /**
     * @var array<string, class-string>
     */
    protected array $bindings;

    /**
     * @param array<string, string> $bindings
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(EntityRegistry $registry, array $bindings)
    {
        $this->registry = $registry;

        $this->bindings = [];
        foreach ($bindings as $entityName => $className) {
            $normalizedEntityName = $this->normalizeEntityName($entityName);
            $normalizedClassName = $this->normalizeClassName($className);
            $this->bindings[$normalizedEntityName] = $normalizedClassName;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws EntityRegistryException
     */
    public function getDescriptorByEntityName(string $entityName): ?FiasEntity
    {
        $normalizedEntityName = $this->normalizeEntityName($entityName);
        $return = null;

        if (isset($this->bindings[$normalizedEntityName]) && $this->registry->hasDescriptor($normalizedEntityName)) {
            $return = $this->registry->getDescriptor($normalizedEntityName);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassByDescriptor(FiasEntity $descriptor): ?string
    {
        $normalizedEntityName = $this->normalizeEntityName($descriptor->getName());

        return $this->bindings[$normalizedEntityName] ?? null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws EntityRegistryException
     */
    public function getDescriptorByInsertFile(string $insertFileName): ?FiasEntity
    {
        $return = null;

        foreach ($this->bindings as $entityName => $className) {
            $descriptor = $this->getDescriptorByEntityName($entityName);
            if ($descriptor && $descriptor->isFileNameFitsXmlInsertFileMask($insertFileName)) {
                $return = $descriptor;
                break;
            }
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     *
     * @throws EntityRegistryException
     */
    public function getDescriptorByDeleteFile(string $insertFileName): ?FiasEntity
    {
        $return = null;

        foreach ($this->bindings as $entityName => $className) {
            $descriptor = $this->getDescriptorByEntityName($entityName);
            if ($descriptor && $descriptor->isFileNameFitsXmlDeleteFileMask($insertFileName)) {
                $return = $descriptor;
                break;
            }
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     *
     * @throws EntityRegistryException
     */
    public function getDescriptorByClass(string $className): ?FiasEntity
    {
        $normalizedClassName = $this->normalizeClassName($className);
        $entityName = null;

        foreach ($this->bindings as $bindEntity => $bindClass) {
            if ($normalizedClassName === $bindClass) {
                $entityName = $bindEntity;
                break;
            }
        }

        return $entityName ? $this->getDescriptorByEntityName($entityName) : null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws EntityRegistryException
     */
    public function getDescriptorByObject(mixed $object): ?FiasEntity
    {
        $return = null;

        if (\is_object($object)) {
            $return = $this->getDescriptorByClass(\get_class($object));
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindedClasses(): array
    {
        return array_unique(array_values($this->bindings));
    }

    /**
     * Приводит имя сущности к единообразному виду.
     */
    protected function normalizeEntityName(string $entityName): string
    {
        return strtolower(trim($entityName));
    }

    /**
     * Приводит имя класса к единообразному виду.
     *
     * @psalm-return class-string
     */
    protected function normalizeClassName(string $className): string
    {
        /** @psalm-var class-string */
        $res = trim($className, '\\ ');

        return $res;
    }
}
