<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityManager;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;

/**
 * Интерфейс для объекта, который содержит соответствия между сущностями ФИАС и их
 * реализациями в конкретном проекте.
 */
interface EntityManager
{
    /**
     * Возвращает дескриптор для сущности по имени сущности из ФИАС.
     */
    public function getDescriptorByEntityName(string $entityName): ?EntityDescriptor;

    /**
     * Ищет класс реализации сущности для указанного дескриптора.
     */
    public function getClassByDescriptor(EntityDescriptor $descriptor): ?string;

    /**
     * Возвращает дескриптор сущности, которая соответствует файлу с данными для загрузки.
     */
    public function getDescriptorByInsertFile(string $insertFileName): ?EntityDescriptor;

    /**
     * Возвращает дескриптор сущности, которая соответствует файлу с данными для удаления.
     */
    public function getDescriptorByDeleteFile(string $insertFileName): ?EntityDescriptor;

    /**
     * Возвращает дескриптор сущности, к которой относится указанный класс.
     */
    public function getDescriptorByClass(string $className): ?EntityDescriptor;

    /**
     * Возвращает дескриптор сущности, к которой относится указанный объект.
     */
    public function getDescriptorByObject(mixed $object): ?EntityDescriptor;

    /**
     * Возвращает список имен классов, у которых есть отношения к сущностям ФИАС.
     *
     * @return string[]
     *
     * @psalm-return class-string[]
     */
    public function getBindedClasses(): array;
}
