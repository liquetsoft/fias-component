<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

/**
 * Интерфейс для объекта, который содержит соответствия между сущностями ФИАС и их
 * реализациями в конкретном проекте.
 */
interface FiasEntityBinder
{
    /**
     * Ищет класс реализации сущности для указанной сущности по имени или объекту.
     *
     * @psalm-return class-string|null
     */
    public function getImplementationByEntityName(FiasEntity|string $entity): ?string;

    /**
     * Возвращает сущность, к которой относится указанный класс или объект.
     *
     * @psalm-param class-string|object $implementation
     */
    public function getEntityByImplementation(string|object $implementation): ?FiasEntity;

    /**
     * Возвращает массив сущностей, которые имеют привязки к реализации.
     *
     * @return FiasEntity[]
     */
    public function getBoundEntities(): array;

    /**
     * Возвращает список имен классов, у которых есть отношения к сущностям ФИАС.
     *
     * @return array<string, string>
     *
     * @psalm-return array<string, class-string>
     */
    public function getBindings(): array;
}
