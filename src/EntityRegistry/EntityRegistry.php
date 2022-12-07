<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityRegistry;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Exception\EntityRegistryException;

/**
 * Интерфейс для объекта, который содержит описание объектов ФИАС.
 */
interface EntityRegistry
{
    /**
     * Возвращает список всех дескрипторов.
     *
     * @return EntityDescriptor[]
     *
     * @throws EntityRegistryException
     */
    public function getDescriptors(): array;

    /**
     * Проверяет существует ли описание сущности с указанным псевдонимом.
     *
     * @param string $entityName
     *
     * @return bool
     *
     * @throws EntityRegistryException
     */
    public function hasDescriptor(string $entityName): bool;

    /**
     * Возвращает описание сущности с указанным псевдонимом.
     *
     * @param string $entityName
     *
     * @return EntityDescriptor
     *
     * @throws \InvalidArgumentException
     * @throws EntityRegistryException
     */
    public function getDescriptor(string $entityName): EntityDescriptor;
}
