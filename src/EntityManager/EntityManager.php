<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityManager;

use Liquetsoft\Fias\Component\FiasEntity\FiasEntity;

/**
 * Интерфейс для объекта, который содержит соответствия между сущностями ФИАС и их
 * реализациями в конкретном проекте.
 */
interface EntityManager
{
    /**
     * Возвращает дескриптор для сущности по имени сущности из ФИАС.
     */
    public function getDescriptorByEntityName(string $entityName): ?FiasEntity;

    /**
     * Ищет класс реализации сущности для указанного дескриптора.
     */
    public function getClassByDescriptor(FiasEntity $descriptor): ?string;

    /**
     * Возвращает дескриптор сущности, которая соответствует файлу с данными для загрузки.
     */
    public function getDescriptorByInsertFile(string $insertFileName): ?FiasEntity;

    /**
     * Возвращает дескриптор сущности, которая соответствует файлу с данными для удаления.
     */
    public function getDescriptorByDeleteFile(string $insertFileName): ?FiasEntity;

    /**
     * Возвращает дескриптор сущности, к которой относится указанный класс.
     */
    public function getDescriptorByClass(string $className): ?FiasEntity;

    /**
     * Возвращает дескриптор сущности, к которой относится указанный объект.
     */
    public function getDescriptorByObject(mixed $object): ?FiasEntity;

    /**
     * Возвращает список имен классов, у которых есть отношения к сущностям ФИАС.
     *
     * @return string[]
     *
     * @psalm-return class-string[]
     */
    public function getBindedClasses(): array;
}
