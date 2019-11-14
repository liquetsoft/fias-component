<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityRegistry;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Exception\EntityRegistryException;
use Throwable;

/**
 * Абстрактный класс для реестра сущностей ФИАС.
 */
abstract class AbstractEntityRegistry implements EntityRegistry
{
    /**
     * @var EntityDescriptor[]|null
     */
    protected $registry;

    /**
     * Возвращает полностюи подготовленный массив с описаниями сущностей.
     *
     * @return EntityDescriptor[]
     */
    abstract protected function createRegistry(): array;

    /**
     * @inheritdoc
     */
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
     * @inheritdoc
     */
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
            throw new InvalidArgumentException(
                "Can't fin entity with name '{$entityName}'."
            );
        }

        return $return;
    }

    /**
     * @inheritdoc
     */
    public function getDescriptors(): array
    {
        if ($this->registry === null) {
            try {
                $this->registry = $this->createRegistry();
            } catch (Throwable $e) {
                $message = 'Error while creating registry.';
                throw new EntityRegistryException($message, 0, $e);
            }
        }

        return $this->registry;
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
