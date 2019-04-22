<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityRegistry;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Exception\EntityRegistryException;
use InvalidArgumentException;

/**
 * Интерфейс для объекта, который содержит описание сушностей.
 */
interface EntityRegistry
{
    /**
     * Проверяет существует ли описание сущности с указанным псевдонимом.
     *
     * @param string $entityName
     *
     * @return bool
     *
     * @throws EntityRegistryException
     */
    public function hasEntityDescriptor(string $entityName): bool;

    /**
     * Возвращает описание сущности с указанным псевдонимом.
     *
     * @param string $entityName
     *
     * @return EntityDescriptor
     *
     * @throws InvalidArgumentException
     * @throws EntityRegistryException
     */
    public function getEntityDescriptor(string $entityName): EntityDescriptor;

    /**
     * Возвращает описание сущнсоти по классу привязанного к ней объекта.
     *
     * @param string $className
     *
     * @return EntityDescriptor
     *
     * @throws InvalidArgumentException
     * @throws EntityRegistryException
     */
    public function getDescriptorForClass(string $className): EntityDescriptor;
}
