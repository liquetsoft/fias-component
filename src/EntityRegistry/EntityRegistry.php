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
     * Проверяет существует ли сущность с указанным псевдонимом.
     *
     * @param string $entityName
     *
     * @return bool
     *
     * @throws EntityRegistryException
     */
    public function hasEntityDescriptor(string $entityName): bool;

    /**
     * Возвращает сущность с указанным псевдонимом.
     *
     * @param string $entityName
     *
     * @return EntityDescriptor
     *
     * @throws InvalidArgumentException
     * @throws EntityRegistryException
     */
    public function getEntityDescriptor(string $entityName): EntityDescriptor;
}
