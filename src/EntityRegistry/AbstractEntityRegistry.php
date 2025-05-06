<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityRegistry;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Exception\EntityRegistryException;

/**
 * Абстрактный класс для реестра сущностей ФИАС.
 */
abstract class AbstractEntityRegistry implements EntityRegistry
{
    /**
     * @var EntityDescriptor[]|null
     */
    protected ?array $registry = null;

    /**
     * Возвращает полностью подготовленный массив с описаниями сущностей.
     *
     * @return EntityDescriptor[]
     */
    abstract protected function createRegistry(): array;

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function hasDescriptor(string $entityName): bool
    {
        $return = false;
        $normalizedName = $this->normalizeEntityName($entityName);

        foreach ($this->getDescriptors() as $descriptor) {
            $normalizedDescriptorName = $this->normalizeEntityName($descriptor->getName());
            if ($normalizedName === $normalizedDescriptorName) {
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
    public function getDescriptor(string $entityName): EntityDescriptor
    {
        $return = null;
        $normalizedName = $this->normalizeEntityName($entityName);

        foreach ($this->getDescriptors() as $descriptor) {
            $normalizedDescriptorName = $this->normalizeEntityName($descriptor->getName());
            if ($normalizedName === $normalizedDescriptorName) {
                $return = $descriptor;
                break;
            }
        }

        if (!$return) {
            throw new \InvalidArgumentException(
                "Can't fin entity with name '{$entityName}'."
            );
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getDescriptors(): array
    {
        if ($this->registry === null) {
            try {
                $this->registry = $this->createRegistry();
            } catch (\Throwable $e) {
                throw new EntityRegistryException($e->getMessage(), 0, $e);
            }
        }

        return $this->registry;
    }

    /**
     * Приводит имена сущностей к единообразному виду.
     */
    public function normalizeEntityName(string $name): string
    {
        return trim(strtolower($name));
    }
}
