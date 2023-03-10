<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityRegistry;

use Liquetsoft\Fias\Component\Exception\EntityRegistryException;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntity;

/**
 * Интерфейс для объекта, который содержит описание объектов ФИАС.
 */
interface EntityRegistry
{
    /**
     * Возвращает список всех дескрипторов.
     *
     * @return FiasEntity[]
     *
     * @throws EntityRegistryException
     */
    public function getDescriptors(): array;

    /**
     * Проверяет существует ли описание сущности с указанным псевдонимом.
     *
     * @throws EntityRegistryException
     */
    public function hasDescriptor(string $entityName): bool;

    /**
     * Возвращает описание сущности с указанным псевдонимом.
     *
     * @throws \InvalidArgumentException
     * @throws EntityRegistryException
     */
    public function getDescriptor(string $entityName): FiasEntity;
}
